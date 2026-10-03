<script setup>
import { computed, ref, watch } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {
    UserPlusIcon,
    HeartIcon,
    MoonIcon,
    ArchiveBoxIcon,
    ChevronLeftIcon,
    ChevronRightIcon,
    ChevronDownIcon,
    LockClosedIcon,
    LockOpenIcon,
    CalendarIcon,
    DocumentTextIcon,
    RectangleStackIcon,
    FolderOpenIcon,
    FunnelIcon,
    MagnifyingGlassIcon,
    XMarkIcon,
    ArrowsUpDownIcon,
    BarsArrowDownIcon,
    BarsArrowUpIcon,
    ArrowPathIcon,
    CheckCircleIcon,
    Squares2X2Icon,
    ListBulletIcon,
    BuildingLibraryIcon,
    PlusIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    type: String,
    registries: Array,   // [{id, year, number, status, reference_prefix, opening_date, closing_date, acts_count, center}]
});

// ─── Filter & Sort State ────────────────────────────────────────────────────────
const selectedYear = ref('all');
const selectedNumber = ref('all');
const selectedStatus = ref('all');
const searchQuery = ref('');
const sortBy = ref('year'); // 'year', 'number', 'acts_count', 'opening_date'
const sortOrder = ref('desc'); // 'asc', 'desc'
const viewMode = ref('grouped'); // 'grouped' (année par année) or 'flat' (numéro par numéro / séquentiel)

// ─── Available Options ─────────────────────────────────────────────────────────
const availableYears = computed(() => {
    const years = new Set(props.registries.map(r => Number(r.year)));
    return Array.from(years).sort((a, b) => b - a);
});

const availableNumbers = computed(() => {
    const numbers = new Set(props.registries.map(r => Number(r.number)));
    return Array.from(numbers).sort((a, b) => a - b);
});

// Count volumes by year
const volumeCountsByYear = computed(() => {
    const counts = {};
    props.registries.forEach(r => {
        counts[r.year] = (counts[r.year] || 0) + 1;
    });
    return counts;
});

// ─── Filter & Sort Pipeline ────────────────────────────────────────────────────
const hasActiveFilters = computed(() => {
    return selectedYear.value !== 'all' ||
           selectedNumber.value !== 'all' ||
           selectedStatus.value !== 'all' ||
           searchQuery.value.trim() !== '' ||
           sortBy.value !== 'year' ||
           sortOrder.value !== 'desc';
});

const resetFilters = () => {
    selectedYear.value = 'all';
    selectedNumber.value = 'all';
    selectedStatus.value = 'all';
    searchQuery.value = '';
    sortBy.value = 'year';
    sortOrder.value = 'desc';
};

const toggleSortOrder = () => {
    sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc';
};

const filteredAndSortedRegistries = computed(() => {
    let result = [...props.registries];

    // Filter by Year (Année par année)
    if (selectedYear.value !== 'all') {
        result = result.filter(r => Number(r.year) === Number(selectedYear.value));
    }

    // Filter by Volume Number (Numéro par numéro)
    if (selectedNumber.value !== 'all') {
        result = result.filter(r => Number(r.number) === Number(selectedNumber.value));
    }

    // Filter by Status
    if (selectedStatus.value !== 'all') {
        result = result.filter(r => r.status === selectedStatus.value);
    }

    // Filter by Search Query
    if (searchQuery.value.trim()) {
        const query = searchQuery.value.toLowerCase().trim();
        result = result.filter(r => {
            const prefix = (r.reference_prefix || '').toLowerCase();
            const center = (r.center?.name || '').toLowerCase();
            const yearStr = String(r.year || '');
            const numStr = String(r.number || '');
            const volumeStr = `volume ${numStr}`.toLowerCase();
            return prefix.includes(query) ||
                   center.includes(query) ||
                   yearStr.includes(query) ||
                   numStr === query ||
                   volumeStr.includes(query);
        });
    }

    // Sorting
    result.sort((a, b) => {
        let valA, valB;
        if (sortBy.value === 'year') {
            valA = Number(a.year) || 0;
            valB = Number(b.year) || 0;
            if (valA !== valB) {
                return sortOrder.value === 'asc' ? valA - valB : valB - valA;
            }
            // Secondary sort by number
            return Number(a.number) - Number(b.number);
        } else if (sortBy.value === 'number') {
            valA = Number(a.number) || 0;
            valB = Number(b.number) || 0;
            if (valA !== valB) {
                return sortOrder.value === 'asc' ? valA - valB : valB - valA;
            }
            // Secondary sort by year
            return Number(b.year) - Number(a.year);
        } else if (sortBy.value === 'acts_count') {
            valA = Number(a.acts_count) || 0;
            valB = Number(b.acts_count) || 0;
            return sortOrder.value === 'asc' ? valA - valB : valB - valA;
        } else if (sortBy.value === 'opening_date') {
            valA = a.opening_date ? new Date(a.opening_date).getTime() : 0;
            valB = b.opening_date ? new Date(b.opening_date).getTime() : 0;
            return sortOrder.value === 'asc' ? valA - valB : valB - valA;
        }
        return 0;
    });

    return result;
});

// Group registries by year for grouped view
const byYear = computed(() => {
    const map = {};
    filteredAndSortedRegistries.value.forEach(r => {
        if (!map[r.year]) map[r.year] = [];
        map[r.year].push(r);
    });

    // Sort years according to sortOrder if sortBy is year, else descending
    const entries = Object.entries(map);
    if (sortBy.value === 'year') {
        entries.sort(([a], [b]) => sortOrder.value === 'asc' ? Number(a) - Number(b) : Number(b) - Number(a));
    } else {
        entries.sort(([a], [b]) => Number(b) - Number(a));
    }

    return entries.map(([year, regs]) => ({
        year: Number(year),
        regs,
        totalActs: regs.reduce((sum, r) => sum + (r.acts_count || 0), 0),
        openCount: regs.filter(r => r.status === 'open').length,
    }));
});

// ─── Type config ──────────────────────────────────────────────────────────────
const typeConfig = computed(() => {
    switch (props.type) {
        case 'naissance':
            return {
                label: 'Naissances',
                sublabel: 'Volumes de registres de naissance',
                icon: UserPlusIcon,
                gradientFrom: '#0EA5E9',
                gradientTo: '#6366F1',
                accent: '#3B82F6',
                accentBg: 'rgba(59,130,246,0.08)',
                accentBorder: 'rgba(59,130,246,0.2)',
                badgeBg: 'bg-blue-50 text-blue-700 border-blue-200',
            };
        case 'mariage':
            return {
                label: 'Mariages',
                sublabel: 'Volumes de registres de mariage',
                icon: HeartIcon,
                gradientFrom: '#F43F5E',
                gradientTo: '#EC4899',
                accent: '#EC4899',
                accentBg: 'rgba(236,72,153,0.08)',
                accentBorder: 'rgba(236,72,153,0.2)',
                badgeBg: 'bg-pink-50 text-pink-700 border-pink-200',
            };
        case 'deces':
            return {
                label: 'Décès',
                sublabel: 'Volumes de registres de décès',
                icon: MoonIcon,
                gradientFrom: '#475569',
                gradientTo: '#1E293B',
                accent: '#475569',
                accentBg: 'rgba(71,85,105,0.08)',
                accentBorder: 'rgba(71,85,105,0.2)',
                badgeBg: 'bg-slate-50 text-slate-700 border-slate-200',
            };
        default:
            return {
                label: 'État Civil',
                sublabel: 'Volumes de registres',
                icon: ArchiveBoxIcon,
                gradientFrom: '#16a34a',
                gradientTo: '#0D9488',
                accent: '#16a34a',
                accentBg: 'rgba(22,163,74,0.08)',
                accentBorder: 'rgba(22,163,74,0.2)',
                badgeBg: 'bg-emerald-50 text-emerald-700 border-emerald-200',
            };
    }
});

const pageTitle = computed(() => `Registres — ${typeConfig.value.label}`);

// Summary stats
const totalActsFiltered = computed(() =>
    filteredAndSortedRegistries.value.reduce((sum, r) => sum + (r.acts_count ?? 0), 0)
);
const openCountFiltered = computed(() =>
    filteredAndSortedRegistries.value.filter(r => r.status === 'open').length
);

const formatDate = (d) => {
    if (!d) return '—';
    return new Date(d).toLocaleDateString('fr-FR', { day: '2-digit', month: 'short', year: 'numeric' });
};
</script>

<template>
    <Head :title="pageTitle" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-2">
                <!-- Breadcrumb -->
                <div class="flex items-center gap-2 text-[10px] font-black uppercase tracking-widest text-gray-400">
                    <Link :href="`/acts/${type}`" class="hover:text-blue-600 transition-colors flex items-center gap-1">
                        <ChevronLeftIcon class="h-3 w-3 stroke-[3]" />
                        {{ typeConfig.label }}
                    </Link>
                    <ChevronRightIcon class="h-3 w-3 stroke-[2]" />
                    <span class="text-gray-600">Registres</span>
                </div>

                <!-- Title row -->
                <div class="flex items-center justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <div
                            class="h-12 w-12 rounded-2xl flex items-center justify-center shadow-lg"
                            :style="`background: linear-gradient(135deg, ${typeConfig.gradientFrom}, ${typeConfig.gradientTo});`"
                        >
                            <ArchiveBoxIcon class="h-6 w-6 text-white" />
                        </div>
                        <div>
                            <h2 class="font-black text-2xl text-gray-900 tracking-tight">Registres — {{ typeConfig.label }}</h2>
                            <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">{{ typeConfig.sublabel }}</p>
                        </div>
                    </div>

                    <Link
                        :href="`/acts/${type}/list`"
                        class="hidden sm:inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-gray-200 hover:border-gray-300 text-gray-700 rounded-xl text-xs font-black uppercase tracking-wider shadow-sm transition-all hover:bg-gray-50 active:scale-95"
                    >
                        <DocumentTextIcon class="h-4 w-4 text-gray-500" />
                        Voir tous les actes
                    </Link>
                </div>
            </div>
        </template>

        <div class="space-y-6">

            <!-- ── Stats Banner ────────────────────────────────────────── -->
            <div
                class="relative overflow-hidden rounded-3xl text-white shadow-xl"
                :style="`background: linear-gradient(135deg, ${typeConfig.gradientFrom} 0%, ${typeConfig.gradientTo} 100%);`"
            >
                <div class="absolute -top-8 -right-8 h-40 w-40 rounded-full opacity-10 bg-white pointer-events-none"></div>
                <div class="absolute -bottom-6 -left-6 h-28 w-28 rounded-full opacity-10 bg-white pointer-events-none"></div>

                <div class="relative px-8 py-7 flex flex-col lg:flex-row items-start lg:items-center justify-between gap-6">
                    <div class="flex items-center gap-4">
                        <div class="h-14 w-14 bg-white/15 rounded-2xl flex items-center justify-center border border-white/20 flex-shrink-0">
                            <ArchiveBoxIcon class="h-8 w-8 text-white" />
                        </div>
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-[0.3em] text-white/70 mb-0.5">Contrôle & Vérification</p>
                            <h1 class="text-2xl font-black">
                                {{ filteredAndSortedRegistries.length }}
                                <span class="text-lg font-bold text-white/80">/ {{ registries.length }}</span> volume{{ registries.length > 1 ? 's' : '' }}
                            </h1>
                            <p class="text-white/80 text-xs font-medium mt-0.5">
                                Vérification séquentielle année par année et numéro par numéro
                            </p>
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-3">
                        <div class="bg-white/15 backdrop-blur-sm border border-white/20 rounded-2xl px-5 py-3 text-center min-w-[110px]">
                            <div class="text-2xl font-black">{{ totalActsFiltered }}</div>
                            <div class="text-[10px] font-bold uppercase tracking-wider text-white/80">Actes signés</div>
                        </div>
                        <div class="bg-white/15 backdrop-blur-sm border border-white/20 rounded-2xl px-5 py-3 text-center min-w-[110px]">
                            <div class="text-2xl font-black">{{ openCountFiltered }}</div>
                            <div class="text-[10px] font-bold uppercase tracking-wider text-white/80">Ouverts</div>
                        </div>
                        <div class="bg-white/15 backdrop-blur-sm border border-white/20 rounded-2xl px-5 py-3 text-center min-w-[110px]">
                            <div class="text-2xl font-black">{{ filteredAndSortedRegistries.length - openCountFiltered }}</div>
                            <div class="text-[10px] font-bold uppercase tracking-wider text-white/80">Clôturés</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── Barre de Navigation "Année par Année" (Onglets Rapides) ── -->
            <div v-if="availableYears.length > 0" class="bg-white rounded-2xl border border-gray-100 p-4 shadow-sm">
                <div class="flex items-center justify-between gap-3 mb-3">
                    <div class="flex items-center gap-2">
                        <CalendarIcon class="h-4 w-4 text-blue-600" />
                        <span class="text-xs font-black uppercase tracking-wider text-gray-800">Vérification Année par Année</span>
                    </div>
                    <span class="text-[11px] font-bold text-gray-400">
                        {{ selectedYear === 'all' ? 'Toutes les années sélectionnées' : `Année ${selectedYear} active` }}
                    </span>
                </div>

                <div class="flex items-center gap-2 overflow-x-auto pb-1 scrollbar-thin">
                    <button
                        type="button"
                        @click="selectedYear = 'all'"
                        class="px-4 py-2 rounded-xl text-xs font-black uppercase tracking-wider transition-all whitespace-nowrap flex items-center gap-2"
                        :class="selectedYear === 'all'
                            ? 'bg-blue-600 text-white shadow-md shadow-blue-200'
                            : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                    >
                        <span>Toutes les années</span>
                        <span
                            class="px-2 py-0.5 rounded-full text-[10px] font-black"
                            :class="selectedYear === 'all' ? 'bg-white/20 text-white' : 'bg-gray-200 text-gray-700'"
                        >
                            {{ registries.length }}
                        </span>
                    </button>

                    <button
                        v-for="yr in availableYears"
                        :key="yr"
                        type="button"
                        @click="selectedYear = yr"
                        class="px-4 py-2 rounded-xl text-xs font-black uppercase tracking-wider transition-all whitespace-nowrap flex items-center gap-2"
                        :class="Number(selectedYear) === Number(yr)
                            ? 'bg-blue-600 text-white shadow-md shadow-blue-200'
                            : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                    >
                        <span>Année {{ yr }}</span>
                        <span
                            class="px-2 py-0.5 rounded-full text-[10px] font-black"
                            :class="Number(selectedYear) === Number(yr) ? 'bg-white/20 text-white' : 'bg-gray-200 text-gray-700'"
                        >
                            {{ volumeCountsByYear[yr] || 0 }} vol.
                        </span>
                    </button>
                </div>
            </div>

            <!-- ── Barre de Filtres et Tri Avancés ("Numéro par Numéro" & Recherche) ── -->
            <div class="bg-white rounded-3xl border border-gray-100 p-6 shadow-sm space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

                    <!-- Recherche Rapide (Volume, Préfixe, Centre) -->
                    <div>
                        <label class="block text-[10px] font-black uppercase tracking-wider text-gray-400 mb-1.5">
                            Recherche
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                <MagnifyingGlassIcon class="h-4 w-4" />
                            </div>
                            <input
                                v-model="searchQuery"
                                type="text"
                                placeholder="N° de volume, préfixe, centre..."
                                class="w-full pl-10 pr-9 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-xs font-medium text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:bg-white transition-all shadow-inner"
                            />
                            <button
                                v-if="searchQuery"
                                @click="searchQuery = ''"
                                type="button"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600"
                            >
                                <XMarkIcon class="h-4 w-4 stroke-[2]" />
                            </button>
                        </div>
                    </div>

                    <!-- Filtre Numéro de Volume ("Numéro par Numéro") -->
                    <div>
                        <label class="block text-[10px] font-black uppercase tracking-wider text-gray-400 mb-1.5">
                            Numéro de Volume
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                <RectangleStackIcon class="h-4 w-4" />
                            </div>
                            <select
                                v-model="selectedNumber"
                                class="w-full pl-10 pr-9 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-xs font-bold text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:bg-white transition-all cursor-pointer appearance-none shadow-sm"
                            >
                                <option value="all">Tous les numéros de volume</option>
                                <option v-for="num in availableNumbers" :key="num" :value="num">
                                    Volume {{ num }}
                                </option>
                            </select>
                            <div class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none text-gray-400">
                                <ChevronDownIcon class="h-4 w-4 stroke-[2]" />
                            </div>
                        </div>
                    </div>

                    <!-- Filtre Statut du Registre -->
                    <div>
                        <label class="block text-[10px] font-black uppercase tracking-wider text-gray-400 mb-1.5">
                            Statut du Registre
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                <LockOpenIcon class="h-4 w-4" />
                            </div>
                            <select
                                v-model="selectedStatus"
                                class="w-full pl-10 pr-9 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-xs font-bold text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:bg-white transition-all cursor-pointer appearance-none shadow-sm"
                            >
                                <option value="all">Tous les statuts</option>
                                <option value="open">Ouverts uniquement</option>
                                <option value="closed">Clôturés uniquement</option>
                            </select>
                            <div class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none text-gray-400">
                                <ChevronDownIcon class="h-4 w-4 stroke-[2]" />
                            </div>
                        </div>
                    </div>

                    <!-- Système de Tri -->
                    <div>
                        <label class="block text-[10px] font-black uppercase tracking-wider text-gray-400 mb-1.5">
                            Critère de Tri
                        </label>
                        <div class="flex items-center gap-1.5">
                            <div class="relative flex-1">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                    <ArrowsUpDownIcon class="h-4 w-4" />
                                </div>
                                <select
                                    v-model="sortBy"
                                    class="w-full pl-9 pr-8 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-xs font-bold text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:bg-white transition-all cursor-pointer appearance-none shadow-sm"
                                >
                                    <option value="year">Trier par Année</option>
                                    <option value="number">Trier par Numéro de volume</option>
                                    <option value="acts_count">Trier par Remplissage (Actes)</option>
                                    <option value="opening_date">Trier par Date d'ouverture</option>
                                </select>
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-gray-400">
                                    <ChevronDownIcon class="h-4 w-4 stroke-[2]" />
                                </div>
                            </div>

                            <!-- Bouton inversion ordre asc/desc -->
                            <button
                                type="button"
                                @click="toggleSortOrder"
                                :title="sortOrder === 'asc' ? 'Ordre croissant (cliquer pour décroissant)' : 'Ordre décroissant (cliquer pour croissant)'"
                                class="p-2.5 bg-gray-50 border border-gray-200 rounded-xl text-gray-600 hover:text-blue-600 hover:bg-blue-50 hover:border-blue-200 transition-all flex items-center justify-center flex-shrink-0 shadow-sm"
                            >
                                <BarsArrowDownIcon v-if="sortOrder === 'desc'" class="h-4 w-4 text-blue-600" />
                                <BarsArrowUpIcon v-else class="h-4 w-4 text-blue-600" />
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Sous-barre : Options d'affichage & Réinitialisation -->
                <div class="pt-3 border-t border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
                    <div class="flex items-center gap-4">
                        <span class="text-gray-500 font-medium">
                            <strong class="text-gray-900 font-black">{{ filteredAndSortedRegistries.length }}</strong> volume(s) trouvé(s)
                            <span v-if="hasActiveFilters" class="text-gray-400">(sur {{ registries.length }} au total)</span>
                        </span>

                        <button
                            v-if="hasActiveFilters"
                            type="button"
                            @click="resetFilters"
                            class="inline-flex items-center gap-1.5 px-3 py-1 bg-rose-50 text-rose-700 hover:bg-rose-100 rounded-lg font-bold text-[11px] transition-all"
                        >
                            <ArrowPathIcon class="h-3 w-3" />
                            Réinitialiser les filtres
                        </button>
                    </div>

                    <!-- Mode d'affichage -->
                    <div class="flex items-center gap-2">
                        <span class="text-[10px] font-black uppercase tracking-wider text-gray-400">Présentation :</span>
                        <div class="inline-flex bg-gray-100 p-1 rounded-xl">
                            <button
                                type="button"
                                @click="viewMode = 'grouped'"
                                class="px-3 py-1 rounded-lg font-black text-[11px] uppercase tracking-wider flex items-center gap-1.5 transition-all"
                                :class="viewMode === 'grouped' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-900'"
                            >
                                <Squares2X2Icon class="h-3.5 w-3.5" />
                                Par Année
                            </button>
                            <button
                                type="button"
                                @click="viewMode = 'flat'"
                                class="px-3 py-1 rounded-lg font-black text-[11px] uppercase tracking-wider flex items-center gap-1.5 transition-all"
                                :class="viewMode === 'flat' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-900'"
                            >
                                <ListBulletIcon class="h-3.5 w-3.5" />
                                Liste Continue
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── Empty State ─────────────────────────────────────────── -->
            <div v-if="filteredAndSortedRegistries.length === 0" class="bg-white rounded-3xl border border-gray-100 shadow-sm px-8 py-20 text-center">
                <div class="flex flex-col items-center gap-4 max-w-md mx-auto">
                    <div class="h-16 w-16 bg-gray-50 rounded-2xl flex items-center justify-center text-gray-300">
                        <ArchiveBoxIcon class="h-8 w-8" />
                    </div>
                    <div>
                        <p class="font-black text-gray-900 text-lg">Aucun registre ne correspond aux critères</p>
                        <p class="text-sm text-gray-400 font-medium mt-1">
                            Essayez de modifier votre sélection d'année, votre numéro de volume ou de réinitialiser vos filtres.
                        </p>
                    </div>
                    <button
                        type="button"
                        @click="resetFilters"
                        class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-black uppercase tracking-wider shadow-lg shadow-blue-100 transition-all active:scale-95"
                    >
                        Réinitialiser tous les filtres
                    </button>
                </div>
            </div>

            <!-- ── MODE 1 : Registries Grouped by Year ("Année par Année") ── -->
            <div v-else-if="viewMode === 'grouped'" class="space-y-8">
                <div v-for="group in byYear" :key="group.year" class="space-y-4">

                    <!-- Year Section Header -->
                    <div class="flex items-center justify-between gap-4 bg-gray-50/80 rounded-2xl px-5 py-3 border border-gray-100">
                        <div class="flex items-center gap-3">
                            <div class="h-8 w-8 rounded-xl bg-blue-600 text-white flex items-center justify-center font-black text-xs shadow-md shadow-blue-200">
                                {{ String(group.year).slice(-2) }}
                            </div>
                            <div>
                                <h3 class="text-sm font-black uppercase tracking-wider text-gray-900">
                                    Registres de l'Année {{ group.year }}
                                </h3>
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">
                                    {{ group.regs.length }} volume{{ group.regs.length > 1 ? 's' : '' }} • {{ group.totalActs }} acte{{ group.totalActs > 1 ? 's' : '' }} enregistré{{ group.totalActs > 1 ? 's' : '' }}
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-emerald-50 text-emerald-700 border border-emerald-200">
                                {{ group.openCount }} ouvert{{ group.openCount > 1 ? 's' : '' }}
                            </span>
                            <span v-if="group.regs.length - group.openCount > 0" class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-gray-100 text-gray-600">
                                {{ group.regs.length - group.openCount }} clos
                            </span>
                        </div>
                    </div>

                    <!-- Volume Cards for this Year -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-5">
                        <div
                            v-for="reg in group.regs"
                            :key="reg.id"
                            class="group relative bg-white rounded-3xl border overflow-hidden transition-all duration-300 hover:-translate-y-1 hover:shadow-xl flex flex-col justify-between"
                            :style="`border-color: ${reg.status === 'open' ? typeConfig.accentBorder : 'rgba(0,0,0,0.08)'};`"
                        >
                            <!-- Top accent bar -->
                            <div
                                class="h-1.5 w-full"
                                :style="reg.status === 'open'
                                    ? `background: linear-gradient(90deg, ${typeConfig.gradientFrom}, ${typeConfig.gradientTo});`
                                    : 'background: #cbd5e1;'"
                            ></div>

                            <div class="p-6 flex-1 flex flex-col justify-between">
                                <!-- Card Header -->
                                <div>
                                    <div class="flex items-start justify-between gap-2 mb-4">
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="h-12 w-12 rounded-2xl flex items-center justify-center flex-shrink-0 transition-transform duration-300 group-hover:scale-110 shadow-sm"
                                                :style="reg.status === 'open'
                                                    ? `background: linear-gradient(135deg, ${typeConfig.gradientFrom}, ${typeConfig.gradientTo});`
                                                    : 'background: #f1f5f9;'"
                                            >
                                                <RectangleStackIcon
                                                    class="h-6 w-6"
                                                    :class="reg.status === 'open' ? 'text-white' : 'text-slate-400'"
                                                />
                                            </div>
                                            <div>
                                                <div class="flex items-center gap-2">
                                                    <span class="text-base font-black text-gray-900 leading-tight">
                                                        Volume {{ reg.number }}
                                                    </span>
                                                    <span class="text-[10px] font-black uppercase px-2 py-0.5 rounded-md bg-gray-100 text-gray-600">
                                                        {{ reg.year }}
                                                    </span>
                                                </div>
                                                <p class="text-[11px] text-gray-500 font-mono font-bold tracking-tight mt-0.5">
                                                    {{ reg.reference_prefix ?? '—' }}
                                                </p>
                                            </div>
                                        </div>

                                        <!-- Status badge -->
                                        <span
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[9px] font-black uppercase tracking-widest flex-shrink-0"
                                            :style="reg.status === 'open'
                                                ? `background: ${typeConfig.accentBg}; color: ${typeConfig.accent};`
                                                : 'background: rgba(0,0,0,0.05); color: #64748b;'"
                                        >
                                            <component
                                                :is="reg.status === 'open' ? LockOpenIcon : LockClosedIcon"
                                                class="h-3 w-3"
                                            />
                                            {{ reg.status === 'open' ? 'Ouvert' : 'Clôturé' }}
                                        </span>
                                    </div>

                                    <!-- Center info if present -->
                                    <div v-if="reg.center" class="flex items-center gap-1.5 text-xs text-gray-500 font-medium mb-4">
                                        <BuildingLibraryIcon class="h-3.5 w-3.5 text-gray-400 flex-shrink-0" />
                                        <span class="truncate">{{ reg.center.name }} ({{ reg.center.code }})</span>
                                    </div>

                                    <!-- Progress & Acts count meter -->
                                    <div
                                        class="rounded-2xl p-4 mb-4"
                                        :style="`background: ${reg.status === 'open' ? typeConfig.accentBg : 'rgba(0,0,0,0.02)'};`"
                                    >
                                        <div class="flex items-center justify-between mb-2">
                                            <div class="flex items-center gap-1.5">
                                                <DocumentTextIcon class="h-4 w-4" :style="`color: ${reg.status === 'open' ? typeConfig.accent : '#64748b'};`" />
                                                <span class="text-xs font-bold text-gray-600">Actes enregistrés</span>
                                            </div>
                                            <span class="text-lg font-black" :style="`color: ${reg.status === 'open' ? typeConfig.accent : '#475569'};`">
                                                {{ reg.acts_count ?? 0 }}
                                                <span class="text-xs font-bold text-gray-400">/ 50</span>
                                            </span>
                                        </div>

                                        <!-- Visual Progress Bar (Capacité 50 actes) -->
                                        <div class="w-full h-2 bg-gray-200/70 rounded-full overflow-hidden">
                                            <div
                                                class="h-full rounded-full transition-all duration-500"
                                                :style="`width: ${Math.min(100, ((reg.acts_count ?? 0) / 50) * 100)}%; background: ${
                                                    (reg.acts_count ?? 0) >= 50
                                                        ? '#10b981'
                                                        : (reg.acts_count ?? 0) > 0
                                                        ? typeConfig.accent
                                                        : '#cbd5e1'
                                                };`"
                                            ></div>
                                        </div>

                                        <div class="flex items-center justify-between text-[10px] font-bold text-gray-400 mt-1.5">
                                            <span>Numérotation : 0001 à {{ String(Math.max(1, reg.acts_count || 1)).padStart(4, '0') }}</span>
                                            <span>{{ Math.round(((reg.acts_count ?? 0) / 50) * 100) }}% plein</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Card Footer: Dates and Verification Button -->
                                <div class="pt-4 border-t border-gray-100 flex items-center justify-between gap-3">
                                    <div class="text-[10px] text-gray-400 font-medium space-y-0.5">
                                        <div><span class="font-bold text-gray-600">Ouvert :</span> {{ formatDate(reg.opening_date) }}</div>
                                        <div v-if="reg.closing_date"><span class="font-bold text-gray-600">Clos :</span> {{ formatDate(reg.closing_date) }}</div>
                                    </div>

                                    <div class="flex items-center gap-2">
                                        <Link
                                            v-if="reg.status === 'open'"
                                            :href="`/acts/${type}/create?old_registry=1&registry_id=${reg.id}`"
                                            class="inline-flex items-center gap-1 px-3 py-2 rounded-xl text-xs font-black uppercase tracking-wider bg-amber-600 hover:bg-amber-700 text-white transition-all shadow-md active:scale-95"
                                            title="Saisir un acte dans ce volume"
                                        >
                                            <PlusIcon class="h-3.5 w-3.5 stroke-[3]" />
                                            <span>Saisir</span>
                                        </Link>

                                        <Link
                                            :href="`/acts/${type}/list?registry_id=${reg.id}&sort_by=number&sort_order=asc`"
                                            class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-black uppercase tracking-wider text-white transition-all shadow-md active:scale-95 group-hover:shadow-lg"
                                            :style="`background: linear-gradient(135deg, ${typeConfig.gradientFrom}, ${typeConfig.gradientTo});`"
                                        >
                                            <span>Vérifier</span>
                                            <ChevronRightIcon class="h-3.5 w-3.5 stroke-[3] transition-transform group-hover:translate-x-0.5" />
                                        </Link>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── MODE 2 : Flat Continuous List ("Numéro par Numéro") ── -->
            <div v-else class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-5">
                <div
                    v-for="reg in filteredAndSortedRegistries"
                    :key="reg.id"
                    class="group relative bg-white rounded-3xl border overflow-hidden transition-all duration-300 hover:-translate-y-1 hover:shadow-xl flex flex-col justify-between"
                    :style="`border-color: ${reg.status === 'open' ? typeConfig.accentBorder : 'rgba(0,0,0,0.08)'};`"
                >
                    <!-- Top accent bar -->
                    <div
                        class="h-1.5 w-full"
                        :style="reg.status === 'open'
                            ? `background: linear-gradient(90deg, ${typeConfig.gradientFrom}, ${typeConfig.gradientTo});`
                            : 'background: #cbd5e1;'"
                    ></div>

                    <div class="p-6 flex-1 flex flex-col justify-between">
                        <div>
                            <div class="flex items-start justify-between gap-2 mb-4">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="h-12 w-12 rounded-2xl flex items-center justify-center flex-shrink-0 shadow-sm"
                                        :style="reg.status === 'open'
                                            ? `background: linear-gradient(135deg, ${typeConfig.gradientFrom}, ${typeConfig.gradientTo});`
                                            : 'background: #f1f5f9;'"
                                    >
                                        <RectangleStackIcon
                                            class="h-6 w-6"
                                            :class="reg.status === 'open' ? 'text-white' : 'text-slate-400'"
                                        />
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="text-base font-black text-gray-900 leading-tight">
                                                Volume {{ reg.number }}
                                            </span>
                                            <span class="text-[10px] font-black uppercase px-2 py-0.5 rounded-md bg-blue-50 text-blue-700 border border-blue-100">
                                                Année {{ reg.year }}
                                            </span>
                                        </div>
                                        <p class="text-[11px] text-gray-500 font-mono font-bold tracking-tight mt-0.5">
                                            {{ reg.reference_prefix ?? '—' }}
                                        </p>
                                    </div>
                                </div>

                                <span
                                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[9px] font-black uppercase tracking-widest flex-shrink-0"
                                    :style="reg.status === 'open'
                                        ? `background: ${typeConfig.accentBg}; color: ${typeConfig.accent};`
                                        : 'background: rgba(0,0,0,0.05); color: #64748b;'"
                                >
                                    <component
                                        :is="reg.status === 'open' ? LockOpenIcon : LockClosedIcon"
                                        class="h-3 w-3"
                                    />
                                    {{ reg.status === 'open' ? 'Ouvert' : 'Clôturé' }}
                                </span>
                            </div>

                            <div v-if="reg.center" class="flex items-center gap-1.5 text-xs text-gray-500 font-medium mb-4">
                                <BuildingLibraryIcon class="h-3.5 w-3.5 text-gray-400 flex-shrink-0" />
                                <span class="truncate">{{ reg.center.name }} ({{ reg.center.code }})</span>
                            </div>

                            <div
                                class="rounded-2xl p-4 mb-4"
                                :style="`background: ${reg.status === 'open' ? typeConfig.accentBg : 'rgba(0,0,0,0.02)'};`"
                            >
                                <div class="flex items-center justify-between mb-2">
                                    <div class="flex items-center gap-1.5">
                                        <DocumentTextIcon class="h-4 w-4" :style="`color: ${reg.status === 'open' ? typeConfig.accent : '#64748b'};`" />
                                        <span class="text-xs font-bold text-gray-600">Actes enregistrés</span>
                                    </div>
                                    <span class="text-lg font-black" :style="`color: ${reg.status === 'open' ? typeConfig.accent : '#475569'};`">
                                        {{ reg.acts_count ?? 0 }}
                                        <span class="text-xs font-bold text-gray-400">/ 50</span>
                                    </span>
                                </div>

                                <div class="w-full h-2 bg-gray-200/70 rounded-full overflow-hidden">
                                    <div
                                        class="h-full rounded-full transition-all duration-500"
                                        :style="`width: ${Math.min(100, ((reg.acts_count ?? 0) / 50) * 100)}%; background: ${
                                            (reg.acts_count ?? 0) >= 50
                                                ? '#10b981'
                                                : (reg.acts_count ?? 0) > 0
                                                ? typeConfig.accent
                                                : '#cbd5e1'
                                        };`"
                                    ></div>
                                </div>
                            </div>
                        </div>

                        <div class="pt-4 border-t border-gray-100 flex items-center justify-between gap-3">
                            <div class="text-[10px] text-gray-400 font-medium">
                                <div><span class="font-bold text-gray-600">Ouvert :</span> {{ formatDate(reg.opening_date) }}</div>
                            </div>

                            <div class="flex items-center gap-2">
                                <Link
                                    v-if="reg.status === 'open'"
                                    :href="`/acts/${type}/create?old_registry=1&registry_id=${reg.id}`"
                                    class="inline-flex items-center gap-1 px-3 py-2 rounded-xl text-xs font-black uppercase tracking-wider bg-amber-600 hover:bg-amber-700 text-white transition-all shadow-md active:scale-95"
                                    title="Saisir un acte dans ce volume"
                                >
                                    <PlusIcon class="h-3.5 w-3.5 stroke-[3]" />
                                    <span>Saisir</span>
                                </Link>

                                <Link
                                    :href="`/acts/${type}/list?registry_id=${reg.id}&sort_by=number&sort_order=asc`"
                                    class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-black uppercase tracking-wider text-white transition-all shadow-md active:scale-95 group-hover:shadow-lg"
                                    :style="`background: linear-gradient(135deg, ${typeConfig.gradientFrom}, ${typeConfig.gradientTo});`"
                                >
                                    <span>Vérifier actes</span>
                                    <ChevronRightIcon class="h-3.5 w-3.5 stroke-[3]" />
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </AuthenticatedLayout>
</template>
