<script setup>
import { computed, ref, watch } from 'vue';
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { 
    PlusIcon, 
    MagnifyingGlassIcon,
    ChevronRightIcon,
    ChevronLeftIcon,
    ChevronDownIcon,
    FingerPrintIcon,
    BuildingLibraryIcon,
    ShieldCheckIcon,
    ArrowUpTrayIcon,
    ArrowDownTrayIcon,
    DocumentTextIcon,
    XMarkIcon,
    ArrowsUpDownIcon,
    BarsArrowDownIcon,
    BarsArrowUpIcon,
    RectangleStackIcon,
    CalendarIcon,
    FunnelIcon,
    ArrowPathIcon,
    LockClosedIcon,
    LockOpenIcon,
    CheckBadgeIcon,
    HashtagIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    acts: Object,
    type: String,
    activeRegistry: Object,           // set when filtered by a specific registry
    availableYears: Array,            // available years for this act type
    availableRegistries: Array,       // all registry volumes for this type
    siblingRegistries: Array,         // other volumes for the same year
    filters: Object,
});

// ─── Filter & Sort States ──────────────────────────────────────────────────────
const search = ref(props.filters?.search || '');
const year = ref(props.filters?.year || (props.activeRegistry?.year ? String(props.activeRegistry.year) : 'all'));
const volumeNumber = ref(props.filters?.volume_number || (props.activeRegistry?.number ? String(props.activeRegistry.number) : 'all'));
const actNumber = ref(props.filters?.act_number || '');
const status = ref(props.filters?.status || (props.activeRegistry ? 'signe' : 'all'));
const sortBy = ref(props.filters?.sort_by || (props.activeRegistry ? 'number' : 'created_at'));
const sortOrder = ref(props.filters?.sort_order || (props.activeRegistry ? 'asc' : 'desc'));

// Debounced search watcher
let searchTimeout = null;
const triggerSearch = () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        applyFilters();
    }, 350);
};

watch(search, () => triggerSearch());
watch(actNumber, () => triggerSearch());

const applyFilters = () => {
    const queryParams = {};

    if (search.value.trim()) {
        queryParams.search = search.value.trim();
    }
    if (actNumber.value.trim()) {
        queryParams.act_number = actNumber.value.trim();
    }
    if (props.activeRegistry?.id) {
        queryParams.registry_id = props.activeRegistry.id;
    } else {
        if (year.value && year.value !== 'all') {
            queryParams.year = year.value;
        }
        if (volumeNumber.value && volumeNumber.value !== 'all') {
            queryParams.volume_number = volumeNumber.value;
        }
    }
    if (status.value && status.value !== 'all') {
        queryParams.status = status.value;
    }
    if (sortBy.value) {
        queryParams.sort_by = sortBy.value;
    }
    if (sortOrder.value) {
        queryParams.sort_order = sortOrder.value;
    }

    router.get(`/acts/${props.type}/list`, queryParams, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const handleHeaderSort = (column) => {
    if (sortBy.value === column) {
        sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortBy.value = column;
        sortOrder.value = (column === 'number' || column === 'name') ? 'asc' : 'desc';
    }
    applyFilters();
};

const clearSearch = () => {
    search.value = '';
    applyFilters();
};

const clearActNumber = () => {
    actNumber.value = '';
    applyFilters();
};

const resetAllFilters = () => {
    search.value = '';
    actNumber.value = '';
    if (!props.activeRegistry) {
        year.value = 'all';
        volumeNumber.value = 'all';
    }
    status.value = props.activeRegistry ? 'signe' : 'all';
    sortBy.value = props.activeRegistry ? 'number' : 'created_at';
    sortOrder.value = props.activeRegistry ? 'asc' : 'desc';
    applyFilters();
};

const hasActiveFilters = computed(() => {
    return Boolean(
        search.value.trim() ||
        actNumber.value.trim() ||
        (year.value && year.value !== 'all' && !props.activeRegistry) ||
        (volumeNumber.value && volumeNumber.value !== 'all' && !props.activeRegistry) ||
        (status.value && status.value !== 'all' && status.value !== (props.activeRegistry ? 'signe' : 'brouillon')) ||
        (sortBy.value !== (props.activeRegistry ? 'number' : 'created_at')) ||
        (sortOrder.value !== (props.activeRegistry ? 'asc' : 'desc'))
    );
});

const canImportExcel = computed(() => {
    const role = usePage().props.auth.user?.role;
    return role === 'Administrateur technique' || role === 'Superviseur / Chef de centre';
});

const title = computed(() => {
    switch (props.type) {
        case 'naissance': return 'Registre des Naissances';
        case 'mariage': return 'Registre des Mariages';
        case 'deces': return 'Registre des Décès';
        default: return 'Registre État-Civil';
    }
});

const icon = computed(() => {
    switch (props.type) {
        case 'naissance': return FingerPrintIcon;
        case 'mariage': return BuildingLibraryIcon;
        case 'deces': return ShieldCheckIcon;
        default: return PlusIcon;
    }
});

const formatName = (act) => {
    if (props.type === 'naissance') return `${act.first_name || ''} ${act.last_name || ''}`.trim() || 'N/A';
    if (props.type === 'mariage') return `${act.husband_first_name || ''} ${act.husband_last_name || ''} & ${act.wife_first_name || ''} ${act.wife_last_name || ''}`.trim() || 'N/A';
    if (props.type === 'deces') return `${act.deceased_first_name || ''} ${act.deceased_last_name || ''}`.trim() || 'N/A';
    return 'N/A';
};

const formatDate = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('fr-FR', {
        day: '2-digit',
        month: 'short',
        year: 'numeric'
    });
};

const getActNumberBadge = (referenceNumber) => {
    if (!referenceNumber) return null;
    const parts = referenceNumber.split('-');
    const suffix = parts[parts.length - 1];
    return suffix;
};

// Excel Import Modal State
const fileInput = ref(null);
const showConfirmModal = ref(false);
const pendingFile = ref(null);

const handleFileUpload = (event) => {
    const file = event.target.files[0];
    if (!file) return;

    pendingFile.value = file;
    showConfirmModal.value = true;
};

const executeImport = () => {
    showConfirmModal.value = false;
    if (!pendingFile.value) return;

    const formData = new FormData();
    formData.append('file', pendingFile.value);

    router.post(`/acts/${props.type}/import`, formData, {
        forceFormData: true,
        onSuccess: () => {
            if (fileInput.value) fileInput.value.value = '';
            pendingFile.value = null;
        },
        onError: (errors) => {
            console.error(errors);
            if (fileInput.value) fileInput.value.value = '';
            pendingFile.value = null;
        }
    });
};

const cancelImport = () => {
    showConfirmModal.value = false;
    pendingFile.value = null;
    if (fileInput.value) fileInput.value.value = '';
};
</script>

<template>
    <Head :title="activeRegistry ? `${title} — Volume ${activeRegistry.number} (${activeRegistry.year})` : title" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-3">
                <!-- Breadcrumb -->
                <div class="flex items-center gap-1.5 text-[10px] font-black uppercase tracking-widest text-gray-400">
                    <Link :href="`/acts/${type}`" class="hover:text-blue-600 transition-colors flex items-center gap-1">
                        <ChevronLeftIcon class="h-3 w-3 stroke-[3]" />
                        {{ type === 'naissance' ? 'Naissances' : type === 'mariage' ? 'Mariages' : 'Décès' }}
                    </Link>
                    <ChevronRightIcon class="h-3 w-3 stroke-[2]" />
                    <Link :href="`/acts/${type}/registres`" class="hover:text-blue-600 transition-colors">Registres</Link>
                    <template v-if="activeRegistry">
                        <ChevronRightIcon class="h-3 w-3 stroke-[2]" />
                        <span class="text-blue-600 font-black">Volume {{ activeRegistry.number }} — {{ activeRegistry.year }}</span>
                    </template>
                    <template v-else>
                        <ChevronRightIcon class="h-3 w-3 stroke-[2]" />
                        <span class="text-gray-600">Tous les actes</span>
                    </template>
                </div>

                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="p-2.5 bg-blue-600 rounded-2xl shadow-lg shadow-blue-200">
                            <component :is="icon" class="h-6 w-6 text-white" />
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h2 class="font-black text-2xl text-gray-900 tracking-tight">
                                    {{ activeRegistry ? `Registre ${activeRegistry.year} — Volume ${activeRegistry.number}` : title }}
                                </h2>
                                <span v-if="activeRegistry" class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider"
                                    :class="activeRegistry.status === 'open' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-gray-100 text-gray-600'"
                                >
                                    {{ activeRegistry.status === 'open' ? 'Ouvert' : 'Clôturé' }}
                                </span>
                            </div>
                            <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">
                                {{ activeRegistry ? `Préfixe officiel : ${activeRegistry.reference_prefix}` : 'Gestion des actes et vérification séquentielle' }}
                            </p>
                        </div>
                    </div>

                    <!-- Action buttons -->
                    <div class="flex flex-wrap items-center gap-2.5">
                        <input
                            type="file"
                            ref="fileInput"
                            class="hidden"
                            accept=".xlsx,.xls,.csv"
                            @change="handleFileUpload"
                        />
                        <a
                            v-if="canImportExcel"
                            :href="`/acts/${type}/template`"
                            class="inline-flex items-center justify-center px-4 py-2.5 bg-white border border-gray-200 rounded-xl font-black text-xs text-green-700 uppercase tracking-widest hover:bg-green-50 shadow-sm transition-all active:scale-95"
                            download
                        >
                            <ArrowDownTrayIcon class="w-4 h-4 mr-1.5" />
                            Modèle Excel
                        </a>
                        <button
                            v-if="canImportExcel"
                            @click="$refs.fileInput.click()"
                            class="inline-flex items-center justify-center px-4 py-2.5 bg-white border border-gray-200 rounded-xl font-black text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50 shadow-sm transition-all active:scale-95"
                        >
                            <ArrowUpTrayIcon class="w-4 h-4 mr-1.5" />
                            Importer Excel
                        </button>
                        <Link
                            :href="activeRegistry ? `/acts/${type}/create?old_registry=1&registry_id=${activeRegistry.id}` : `/acts/${type}/create?old_registry=1`"
                            class="inline-flex items-center justify-center px-4 py-2.5 bg-white border border-gray-200 rounded-xl font-black text-xs text-amber-600 uppercase tracking-widest hover:bg-amber-50 shadow-sm transition-all active:scale-95"
                        >
                            <DocumentTextIcon class="w-4 h-4 mr-1.5" />
                            <span>{{ activeRegistry ? `Saisir (Vol. ${activeRegistry.number} - ${activeRegistry.year})` : 'Saisir Ancien Registre' }}</span>
                        </Link>
                        <Link
                            :href="`/acts/${type}/create`"
                            class="inline-flex items-center justify-center px-5 py-2.5 bg-blue-600 border border-transparent rounded-xl font-black text-xs text-white uppercase tracking-widest hover:bg-blue-700 shadow-xl shadow-blue-100 transition-all active:scale-95"
                        >
                            <PlusIcon class="w-4 h-4 mr-1.5 stroke-[3]" />
                            Nouvel Acte
                        </Link>
                    </div>
                </div>
            </div>
        </template>

        <div class="space-y-6">

            <!-- ── Bannière de Vérification du Volume (si actif) ────────── -->
            <div v-if="activeRegistry" class="bg-gradient-to-r from-blue-900 to-indigo-900 text-white rounded-3xl p-6 shadow-xl relative overflow-hidden">
                <div class="absolute -right-10 -bottom-10 w-44 h-44 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>

                <div class="relative flex flex-col md:flex-row md:items-center justify-between gap-5">
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <RectangleStackIcon class="h-5 w-5 text-blue-300" />
                            <span class="text-xs font-black uppercase tracking-[0.25em] text-blue-200">
                                Mode Vérification Registre
                            </span>
                        </div>
                        <h3 class="text-xl font-black">
                            Volume {{ activeRegistry.number }} — Registre Annuel {{ activeRegistry.year }}
                        </h3>
                        <p class="text-xs text-blue-200/80 mt-1">
                            Centre : <strong class="text-white">{{ activeRegistry.center?.name || 'Centre d\'État Civil' }}</strong>
                            • Préfixe : <span class="font-mono bg-white/15 px-2 py-0.5 rounded text-white">{{ activeRegistry.reference_prefix }}</span>
                        </p>
                    </div>

                    <!-- Volumes de la même année pour basculer facilement -->
                    <div v-if="siblingRegistries && siblingRegistries.length > 1" class="flex flex-col items-start md:items-end gap-1.5">
                        <span class="text-[10px] font-black uppercase tracking-wider text-blue-200">
                            Volumes de l'année {{ activeRegistry.year }} :
                        </span>
                        <div class="flex items-center gap-1.5 overflow-x-auto pb-1">
                            <Link
                                v-for="sib in siblingRegistries"
                                :key="sib.id"
                                :href="`/acts/${type}/list?registry_id=${sib.id}&sort_by=number&sort_order=asc`"
                                class="px-3 py-1.5 rounded-xl text-xs font-black transition-all flex items-center gap-1.5"
                                :class="sib.id === activeRegistry.id
                                    ? 'bg-white text-blue-900 shadow-md shadow-black/20 font-black'
                                    : 'bg-white/15 text-white hover:bg-white/25'"
                            >
                                <span>Vol. {{ sib.number }}</span>
                                <span class="text-[9px] opacity-70">({{ sib.year }})</span>
                            </Link>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── Barre de Filtres & Tri (Année par Année, Numéro par Numéro) ── -->
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-5 space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3.5">

                    <!-- Recherche Globale -->
                    <div class="lg:col-span-2">
                        <label class="block text-[10px] font-black uppercase tracking-wider text-gray-400 mb-1">
                            Recherche par nom, prénom ou référence
                        </label>
                        <div class="relative">
                            <MagnifyingGlassIcon class="absolute left-3.5 inset-y-0 my-auto h-4 w-4 text-gray-400 pointer-events-none" />
                            <input
                                v-model="search"
                                type="text"
                                placeholder="Rechercher un acte..."
                                class="w-full pl-10 pr-9 py-2.5 bg-gray-50/80 border border-gray-200 rounded-xl text-xs font-medium text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:bg-white transition-all shadow-inner"
                            />
                            <button
                                v-if="search"
                                @click="clearSearch"
                                type="button"
                                class="absolute right-3 inset-y-0 my-auto text-gray-400 hover:text-gray-600 p-0.5"
                            >
                                <XMarkIcon class="h-4 w-4 stroke-[2]" />
                            </button>
                        </div>
                    </div>

                    <!-- Filtre Numéro d'Acte Précis ("Numéro par Numéro") -->
                    <div>
                        <label class="block text-[10px] font-black uppercase tracking-wider text-gray-400 mb-1">
                            N° d'Acte précis
                        </label>
                        <div class="relative">
                            <HashtagIcon class="absolute left-3 inset-y-0 my-auto h-4 w-4 text-gray-400 pointer-events-none" />
                            <input
                                v-model="actNumber"
                                type="text"
                                placeholder="Ex: 1, 0005, 12..."
                                class="w-full pl-9 pr-8 py-2.5 bg-gray-50/80 border border-gray-200 rounded-xl text-xs font-bold text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:bg-white transition-all shadow-inner"
                            />
                            <button
                                v-if="actNumber"
                                @click="clearActNumber"
                                type="button"
                                class="absolute right-3 inset-y-0 my-auto text-gray-400 hover:text-gray-600 p-0.5"
                            >
                                <XMarkIcon class="h-4 w-4 stroke-[2]" />
                            </button>
                        </div>
                    </div>

                    <!-- Filtre Année (si hors registre spécifique) ou Registre Sélecteur -->
                    <div v-if="!activeRegistry">
                        <label class="block text-[10px] font-black uppercase tracking-wider text-gray-400 mb-1">
                            Année par Année
                        </label>
                        <div class="relative">
                            <CalendarIcon class="absolute left-3 inset-y-0 my-auto h-4 w-4 text-gray-400 pointer-events-none" />
                            <select
                                v-model="year"
                                @change="applyFilters"
                                class="w-full pl-9 pr-8 py-2.5 bg-gray-50/80 border border-gray-200 rounded-xl text-xs font-bold text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:bg-white transition-all appearance-none cursor-pointer"
                            >
                                <option value="all">Toutes les années</option>
                                <option v-for="yr in availableYears" :key="yr" :value="yr">
                                    Année {{ yr }}
                                </option>
                            </select>
                            <ChevronDownIcon class="absolute right-3 inset-y-0 my-auto h-4 w-4 text-gray-400 pointer-events-none" />
                        </div>
                    </div>

                    <!-- Filtre Statut de l'Acte -->
                    <div>
                        <label class="block text-[10px] font-black uppercase tracking-wider text-gray-400 mb-1">
                            Statut de l'Acte
                        </label>
                        <div class="relative">
                            <select
                                v-model="status"
                                @change="applyFilters"
                                class="w-full px-3.5 py-2.5 bg-gray-50/80 border border-gray-200 rounded-xl text-xs font-bold text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:bg-white transition-all appearance-none cursor-pointer"
                            >
                                <option value="all">Tous les statuts</option>
                                <option value="signe">Signé (Au registre)</option>
                                <option value="valide_hierarchie">Validé hiérarchie</option>
                                <option value="brouillon">Brouillon</option>
                            </select>
                            <ChevronDownIcon class="absolute right-3 inset-y-0 my-auto h-4 w-4 text-gray-400 pointer-events-none" />
                        </div>
                    </div>

                    <!-- Système de Tri -->
                    <div :class="activeRegistry ? 'sm:col-span-2' : ''">
                        <label class="block text-[10px] font-black uppercase tracking-wider text-gray-400 mb-1">
                            Tri des Actes
                        </label>
                        <div class="flex items-center gap-1.5">
                            <div class="relative flex-1">
                                <ArrowsUpDownIcon class="absolute left-3 inset-y-0 my-auto h-4 w-4 text-gray-400 pointer-events-none" />
                                <select
                                    v-model="sortBy"
                                    @change="applyFilters"
                                    class="w-full pl-9 pr-8 py-2.5 bg-gray-50/80 border border-gray-200 rounded-xl text-xs font-bold text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:bg-white transition-all appearance-none cursor-pointer"
                                >
                                    <option value="number">Par Numéro d'Acte</option>
                                    <option value="created_at">Par Date d'enregistrement</option>
                                    <option value="date">Par Date de l'évènement</option>
                                    <option value="name">Par Nom du titulaire</option>
                                </select>
                                <ChevronDownIcon class="absolute right-3 inset-y-0 my-auto h-4 w-4 text-gray-400 pointer-events-none" />
                            </div>

                            <button
                                type="button"
                                @click="handleHeaderSort(sortBy)"
                                :title="sortOrder === 'asc' ? 'Ordre croissant (1 -> N). Cliquer pour inverser.' : 'Ordre décroissant (N -> 1). Cliquer pour inverser.'"
                                class="p-2.5 bg-gray-50 border border-gray-200 rounded-xl text-blue-600 hover:bg-blue-50 transition-all flex items-center justify-center shadow-sm"
                            >
                                <BarsArrowUpIcon v-if="sortOrder === 'asc'" class="h-4 w-4" />
                                <BarsArrowDownIcon v-else class="h-4 w-4" />
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Footer de barre de filtres avec reset -->
                <div class="pt-3 border-t border-gray-100 flex items-center justify-between text-xs">
                    <span class="text-gray-500 font-medium">
                        <strong class="text-gray-900 font-black">{{ acts.total || acts.data.length }}</strong> acte(s) trouvé(s)
                        <span v-if="activeRegistry" class="text-blue-600 font-bold ml-1">• Volume {{ activeRegistry.number }}</span>
                    </span>

                    <button
                        v-if="hasActiveFilters"
                        type="button"
                        @click="resetAllFilters"
                        class="inline-flex items-center gap-1.5 px-3 py-1 bg-rose-50 text-rose-700 hover:bg-rose-100 rounded-lg font-bold text-[11px] transition-all"
                    >
                        <ArrowPathIcon class="h-3 w-3" />
                        Réinitialiser filtres & tri
                    </button>
                </div>
            </div>

            <!-- ── Tableau des Actes Vérifiables ───────────────────────── -->
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                <table class="min-w-full divide-y divide-gray-100">
                    <thead class="bg-gray-50/70">
                        <tr>
                            <!-- Numéro / Référence (Cliquable pour tri) -->
                            <th 
                                @click="handleHeaderSort('number')"
                                class="px-8 py-5 text-left text-[10px] font-black uppercase tracking-widest cursor-pointer select-none group"
                                :class="sortBy === 'number' ? 'text-blue-600' : 'text-gray-400 hover:text-gray-700'"
                            >
                                <div class="flex items-center gap-1.5">
                                    <span>N° d'Acte & Référence</span>
                                    <span v-if="sortBy === 'number'" class="text-blue-600">
                                        {{ sortOrder === 'asc' ? '↑' : '↓' }}
                                    </span>
                                    <ArrowsUpDownIcon v-else class="h-3 w-3 opacity-0 group-hover:opacity-100 transition-opacity" />
                                </div>
                            </th>

                            <!-- Titulaires / Sujet (Cliquable pour tri) -->
                            <th 
                                @click="handleHeaderSort('name')"
                                class="px-8 py-5 text-left text-[10px] font-black uppercase tracking-widest cursor-pointer select-none group"
                                :class="sortBy === 'name' ? 'text-blue-600' : 'text-gray-400 hover:text-gray-700'"
                            >
                                <div class="flex items-center gap-1.5">
                                    <span>Sujet / Titulaires</span>
                                    <span v-if="sortBy === 'name'" class="text-blue-600">
                                        {{ sortOrder === 'asc' ? '↑' : '↓' }}
                                    </span>
                                    <ArrowsUpDownIcon v-else class="h-3 w-3 opacity-0 group-hover:opacity-100 transition-opacity" />
                                </div>
                            </th>

                            <!-- Date Événement (Cliquable pour tri) -->
                            <th 
                                @click="handleHeaderSort('date')"
                                class="px-8 py-5 text-left text-[10px] font-black uppercase tracking-widest cursor-pointer select-none group"
                                :class="sortBy === 'date' ? 'text-blue-600' : 'text-gray-400 hover:text-gray-700'"
                            >
                                <div class="flex items-center gap-1.5">
                                    <span>Date Évènement</span>
                                    <span v-if="sortBy === 'date'" class="text-blue-600">
                                        {{ sortOrder === 'asc' ? '↑' : '↓' }}
                                    </span>
                                    <ArrowsUpDownIcon v-else class="h-3 w-3 opacity-0 group-hover:opacity-100 transition-opacity" />
                                </div>
                            </th>

                            <th class="px-8 py-5 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">
                                Statut
                            </th>

                            <th class="px-8 py-5 text-right text-[10px] font-black text-gray-400 uppercase tracking-widest">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <tr v-for="act in acts.data" :key="act.id" class="group hover:bg-blue-50/40 transition-colors">
                            <!-- N° d'acte & Référence -->
                            <td class="px-8 py-5">
                                <div class="flex items-center gap-2.5">
                                    <span class="px-2.5 py-1 bg-blue-100 text-blue-900 rounded-lg text-xs font-black font-mono shadow-sm">
                                        {{ getActNumberBadge(act.reference_number) ? `N° ${getActNumberBadge(act.reference_number)}` : 'N/A' }}
                                    </span>
                                    <span class="text-xs font-medium text-gray-500 font-mono">
                                        {{ act.reference_number || 'SANS RÉF' }}
                                    </span>
                                </div>
                            </td>

                            <!-- Sujet / Titulaires -->
                            <td class="px-8 py-5">
                                <div class="text-sm font-black text-gray-900 group-hover:text-blue-700 transition-colors">
                                    {{ formatName(act) }}
                                </div>
                                <div class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">
                                    {{ act.registry ? `Volume ${act.registry.number} (${act.registry.year})` : 'Hors volume' }}
                                </div>
                            </td>

                            <!-- Date évènement -->
                            <td class="px-8 py-5 text-sm text-gray-600 font-medium">
                                {{ formatDate(act.date_of_birth || act.marriage_date || act.date_of_death) }}
                            </td>

                            <!-- Statut -->
                            <td class="px-8 py-5">
                                <span class="px-3 py-1 inline-flex text-[9px] font-black uppercase rounded-full"
                                    :class="{
                                        'bg-gray-100 text-gray-500': act.status === 'brouillon',
                                        'bg-green-100 text-green-700 border border-green-200': act.status === 'signe',
                                        'bg-blue-100 text-blue-700 border border-blue-200': act.status === 'valide_hierarchie'
                                    }"
                                >
                                    {{ act.status === 'signe' ? 'Signé au registre' : act.status === 'valide_hierarchie' ? 'Validé' : act.status }}
                                </span>
                            </td>

                            <!-- Actions -->
                            <td class="px-8 py-5 text-right">
                                <Link
                                    :href="`/acts/${type}/${act.id}`"
                                    class="inline-flex items-center text-blue-600 hover:text-blue-900 font-black text-xs uppercase tracking-wider group-hover:translate-x-0.5 transition-all"
                                >
                                    <span>Consulter</span>
                                    <ChevronRightIcon class="ml-1 h-3.5 w-3.5 stroke-[3]" />
                                </Link>
                            </td>
                        </tr>

                        <!-- Empty state -->
                        <tr v-if="acts.data.length === 0">
                            <td colspan="5" class="px-8 py-20 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    <div class="h-12 w-12 bg-gray-50 rounded-2xl flex items-center justify-center">
                                        <component :is="icon" class="h-6 w-6 text-gray-300" />
                                    </div>
                                    <p class="text-sm font-black text-gray-600">Aucun acte trouvé</p>
                                    <p class="text-xs text-gray-400 font-medium max-w-sm">
                                        Aucun acte ne correspond aux critères de filtre sélectionnés dans ce registre.
                                    </p>
                                    <button
                                        v-if="hasActiveFilters"
                                        type="button"
                                        @click="resetAllFilters"
                                        class="mt-2 text-xs font-black uppercase text-blue-600 hover:text-blue-800 tracking-wider"
                                    >
                                        Effacer les filtres
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- Pagination Inertia -->
                <div v-if="acts.links && acts.links.length > 3" class="px-8 py-5 border-t border-gray-100 bg-gray-50/50 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">
                        Page {{ acts.current_page }} sur {{ acts.last_page }} ({{ acts.total }} actes)
                    </span>
                    <div class="flex items-center gap-1 overflow-x-auto">
                        <Component
                            v-for="(link, key) in acts.links"
                            :key="key"
                            :is="link.url ? Link : 'span'"
                            :href="link.url"
                            v-html="link.label"
                            class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all"
                            :class="{
                                'bg-blue-600 text-white shadow-md shadow-blue-200': link.active,
                                'text-gray-600 hover:bg-gray-200/70': link.url && !link.active,
                                'text-gray-300 cursor-not-allowed': !link.url
                            }"
                        />
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Custom Confirm Modal for Excel Import -->
        <Teleport to="body">
          <Transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
          >
            <div v-if="showConfirmModal" class="fixed inset-0 z-50 overflow-y-auto bg-gray-900/60 backdrop-blur-sm flex items-center justify-center p-4">
              <div 
                class="bg-white rounded-3xl overflow-hidden shadow-2xl transform transition-all max-w-lg w-full border border-gray-100"
              >
                <div class="p-8 flex items-start gap-6">
                  <div class="flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-2xl bg-blue-50 text-blue-600">
                    <ArrowUpTrayIcon class="h-6 w-6" />
                  </div>
                  <div class="flex-1 min-w-0">
                    <h3 class="text-lg font-black text-gray-900 leading-tight">
                      Importation du registre
                    </h3>
                    <p class="mt-2 text-sm text-gray-500 font-medium">
                      Voulez-vous vraiment importer ce fichier dans le registre des {{ props.type }}s ?
                    </p>
                    
                    <div class="mt-8 flex justify-end gap-3">
                      <button
                        type="button"
                        @click="cancelImport"
                        class="px-6 py-3 bg-white border border-gray-200 rounded-xl font-black text-[10px] text-gray-500 uppercase tracking-widest hover:bg-gray-50 transition-all active:scale-95"
                      >
                        Annuler
                      </button>
                      <button
                        type="button"
                        @click="executeImport"
                        class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-black text-[10px] uppercase tracking-widest shadow-xl shadow-blue-100 transition-all active:scale-95"
                      >
                        Importer
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </Transition>
        </Teleport>
    </AuthenticatedLayout>
</template>
