<script setup>
import { ref, watch, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {
    PlusIcon,
    MagnifyingGlassIcon,
    ChevronRightIcon,
    ChevronLeftIcon,
    DocumentCheckIcon,
    FunnelIcon,
    XMarkIcon,
    EyeIcon,
    PencilSquareIcon,
    CheckCircleIcon,
    ClockIcon,
    ExclamationTriangleIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
    certificates: Object,
    types: Array,
    can_create: Boolean,
    filters: Object,
    stats: Object,
});

const search = ref(props.filters?.search || '');
const selectedType = ref(props.filters?.type || '');

let searchTimeout = null;
const updateFilters = () => {
    router.get(
        route('civil-certificates.index'),
        {
            search: search.value || undefined,
            type: selectedType.value || undefined,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        }
    );
};

watch(search, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(updateFilters, 300);
});

watch(selectedType, () => {
    updateFilters();
});

const clearFilters = () => {
    search.value = '';
    selectedType.value = '';
    updateFilters();
};

const hasActiveFilters = computed(() => {
    return !!search.value || !!selectedType.value;
});

const formatType = (type) => {
    const dictionary = {
        residence: 'Certificat de résidence',
        coutume: 'Certificat de coutume',
        indigence: 'Certificat d\'indigence',
        individualite: 'Certificat d\'individualité',
        vie_collective: 'Certificat de vie collective',
        vie_individuel: 'Certificat de vie individuelle',
        non_inscrit_naissance: 'Certificat de non inscrit de naissance',
        acte_non_inexistant: 'Certificat d\'acte non inexistant',
    };
    return dictionary[type] || type;
};

const formatStatus = (status) => {
    const dict = {
        brouillon: 'Brouillon',
        observation: 'En observation',
        a_corriger: 'À corriger',
        valide_hierarchie: 'Validé Hiérarchie',
        signe: 'Délivré & Signé',
    };
    return dict[status] || status;
};

const getStatusBadgeStyle = (status) => {
    switch (status) {
        case 'signe':
            return 'bg-emerald-50 text-emerald-700 border-emerald-200/60';
        case 'valide_hierarchie':
            return 'bg-blue-50 text-blue-700 border-blue-200/60';
        case 'observation':
        case 'a_corriger':
            return 'bg-amber-50 text-amber-700 border-amber-200/60';
        default:
            return 'bg-slate-100 text-slate-600 border-slate-200/60';
    }
};

const getStatusIcon = (status) => {
    switch (status) {
        case 'signe':
            return CheckCircleIcon;
        case 'valide_hierarchie':
            return CheckCircleIcon;
        case 'observation':
        case 'a_corriger':
            return ExclamationTriangleIcon;
        default:
            return ClockIcon;
    }
};
</script>

<template>
    <Head title="Registre des Certificats Civils" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-2">
                <!-- Breadcrumb -->
                <div class="flex items-center gap-2 text-[10px] font-black uppercase tracking-widest text-gray-400">
                    <span class="text-gray-400">Services Certificats</span>
                    <ChevronRightIcon class="h-3 w-3 stroke-[2]" />
                    <span class="text-gray-600">Certificats Civils</span>
                </div>

                <!-- Title row -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <div class="h-12 w-12 rounded-2xl flex items-center justify-center shadow-lg bg-gradient-to-br from-indigo-500 to-blue-600">
                            <DocumentCheckIcon class="h-6 w-6 text-white" />
                        </div>
                        <div>
                            <h2 class="font-black text-2xl text-gray-900 tracking-tight">Registre des Certificats Civils</h2>
                            <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Délivrance et suivi des certificats administratifs officiels</p>
                        </div>
                    </div>

                    <Link
                        v-if="can_create"
                        :href="route('civil-certificates.create')"
                        class="inline-flex items-center justify-center px-6 py-3.5 bg-gradient-to-r from-blue-600 to-indigo-600 border border-transparent rounded-2xl font-black text-xs text-white uppercase tracking-widest hover:from-blue-700 hover:to-indigo-700 shadow-lg shadow-blue-200 transition-all active:scale-95"
                    >
                        <PlusIcon class="w-4 h-4 mr-2 stroke-[3]" />
                        Nouvelle Demande
                    </Link>
                </div>
            </div>
        </template>

        <div class="space-y-6">

            <!-- ── Stats Banner ────────────────────────────────────────────── -->
            <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-slate-900 via-indigo-950 to-blue-900 text-white shadow-xl p-7">
                <div class="absolute -top-10 -right-10 h-44 w-44 rounded-full opacity-10 bg-white"></div>
                <div class="absolute -bottom-10 -left-10 h-32 w-32 rounded-full opacity-10 bg-white"></div>

                <div class="relative flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
                    <div class="flex items-center gap-4">
                        <div class="h-14 w-14 bg-white/10 backdrop-blur-md rounded-2xl flex items-center justify-center border border-white/20 flex-shrink-0">
                            <DocumentCheckIcon class="h-8 w-8 text-blue-300" />
                        </div>
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-[0.3em] text-blue-300 mb-0.5">Certificats Administratifs</p>
                            <h1 class="text-2xl font-black text-white">Registre Officiel des Certificats</h1>
                            <p class="text-white/70 text-xs font-medium mt-0.5">Suivi de l'instruction, de la validation hiérarchique et des signatures</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-3 w-full md:w-auto">
                        <div class="bg-white/10 backdrop-blur-md border border-white/15 rounded-2xl px-5 py-3 text-center">
                            <div class="text-2xl font-black text-white">{{ stats?.total ?? certificates.total ?? 0 }}</div>
                            <div class="text-[10px] font-bold uppercase tracking-wider text-blue-200">Total Demandes</div>
                        </div>
                        <div class="bg-white/10 backdrop-blur-md border border-white/15 rounded-2xl px-5 py-3 text-center">
                            <div class="text-2xl font-black text-emerald-400">{{ stats?.signed ?? 0 }}</div>
                            <div class="text-[10px] font-bold uppercase tracking-wider text-emerald-200">Signés / Délivrés</div>
                        </div>
                        <div class="bg-white/10 backdrop-blur-md border border-white/15 rounded-2xl px-5 py-3 text-center">
                            <div class="text-2xl font-black text-amber-300">{{ stats?.pending ?? 0 }}</div>
                            <div class="text-[10px] font-bold uppercase tracking-wider text-amber-200">En cours</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── Search & Filter Bar ──────────────────────────────────────── -->
            <div class="bg-white rounded-3xl border border-gray-100 p-5 shadow-sm space-y-4">
                <div class="flex flex-col md:flex-row items-stretch md:items-center justify-between gap-4">
                    <!-- Search Input -->
                    <div class="relative flex-1">
                        <MagnifyingGlassIcon class="absolute left-4 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400 stroke-[2]" />
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Rechercher par numéro de référence, nom ou prénom du demandeur..."
                            class="w-full pl-12 pr-10 py-3 bg-gray-50/70 border border-gray-200 rounded-2xl text-xs font-semibold text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:bg-white transition-all shadow-inner"
                        />
                        <button
                            v-if="search"
                            @click="search = ''"
                            type="button"
                            class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors p-1"
                        >
                            <XMarkIcon class="h-4 w-4 stroke-[2]" />
                        </button>
                    </div>

                    <!-- Type Dropdown -->
                    <div class="relative min-w-[240px]">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                            <FunnelIcon class="h-4 w-4 stroke-[2]" />
                        </div>
                        <select
                            v-model="selectedType"
                            class="w-full pl-10 pr-10 py-3 bg-gray-50/70 border border-gray-200 rounded-2xl text-xs font-black uppercase tracking-wider text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:bg-white transition-all cursor-pointer appearance-none shadow-sm"
                        >
                            <option value="">Tous les types ({{ types?.length ?? 0 }})</option>
                            <option v-for="t in types" :key="t" :value="t">
                                {{ formatType(t) }}
                            </option>
                        </select>
                        <div class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none text-gray-400">
                            <ChevronRightIcon class="h-4 w-4 rotate-90 stroke-[2]" />
                        </div>
                    </div>

                    <!-- Clear filters button -->
                    <button
                        v-if="hasActiveFilters"
                        @click="clearFilters"
                        type="button"
                        class="px-4 py-3 bg-gray-100 hover:bg-gray-200 text-gray-600 font-bold text-xs rounded-2xl transition-all flex items-center justify-center gap-1.5"
                    >
                        <XMarkIcon class="h-4 w-4 stroke-[2]" />
                        Réinitialiser
                    </button>
                </div>
            </div>

            <!-- ── Table Container ─────────────────────────────────────────── -->
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-gray-50/60">
                            <tr>
                                <th class="px-8 py-5 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Référence</th>
                                <th class="px-8 py-5 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Type de Certificat</th>
                                <th class="px-8 py-5 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Demandeur</th>
                                <th class="px-8 py-5 text-center text-[10px] font-black text-gray-400 uppercase tracking-widest">Statut</th>
                                <th class="px-8 py-5 text-right text-[10px] font-black text-gray-400 uppercase tracking-widest">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <tr v-for="cert in certificates.data" :key="cert.id" class="group hover:bg-blue-50/30 transition-colors">
                                <!-- Reference -->
                                <td class="px-8 py-5">
                                    <span class="inline-flex items-center text-xs font-black font-mono text-blue-900 bg-blue-50 border border-blue-100 px-3 py-1 rounded-xl shadow-xs">
                                        {{ cert.reference_number || 'SANS RÉF' }}
                                    </span>
                                </td>

                                <!-- Type -->
                                <td class="px-8 py-5">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-extrabold bg-indigo-50 text-indigo-700 border border-indigo-100">
                                        {{ formatType(cert.type) }}
                                    </span>
                                </td>

                                <!-- Applicant -->
                                <td class="px-8 py-5">
                                    <div class="text-sm font-black text-gray-900">
                                        {{ cert.applicant_first_name }} {{ cert.applicant_last_name }}
                                    </div>
                                    <div v-if="cert.applicant_cni" class="text-[10px] font-mono font-bold text-gray-400">
                                        CNI: {{ cert.applicant_cni }}
                                    </div>
                                </td>

                                <!-- Status -->
                                <td class="px-8 py-5 text-center">
                                    <span
                                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider border shadow-xs"
                                        :class="getStatusBadgeStyle(cert.status)"
                                    >
                                        <component :is="getStatusIcon(cert.status)" class="h-3.5 w-3.5" />
                                        {{ formatStatus(cert.status) }}
                                    </span>
                                </td>

                                <!-- Actions -->
                                <td class="px-8 py-5 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <Link
                                            :href="route('civil-certificates.show', cert.id)"
                                            class="inline-flex items-center gap-1 px-3 py-2 bg-blue-50 text-blue-700 hover:bg-blue-600 hover:text-white rounded-xl font-bold text-xs transition-all active:scale-95 shadow-xs"
                                        >
                                            <EyeIcon class="h-3.5 w-3.5 stroke-[2]" />
                                            Consulter
                                        </Link>
                                        <Link
                                            v-if="['brouillon', 'a_corriger'].includes(cert.status)"
                                            :href="route('civil-certificates.edit', cert.id)"
                                            class="inline-flex items-center gap-1 px-3 py-2 bg-gray-100 text-gray-700 hover:bg-gray-800 hover:text-white rounded-xl font-bold text-xs transition-all active:scale-95 shadow-xs"
                                        >
                                            <PencilSquareIcon class="h-3.5 w-3.5 stroke-[2]" />
                                            Modifier
                                        </Link>
                                    </div>
                                </td>
                            </tr>

                            <!-- Empty state -->
                            <tr v-if="certificates.data.length === 0">
                                <td colspan="5" class="px-8 py-20 text-center">
                                    <div class="flex flex-col items-center gap-4">
                                        <div class="h-16 w-16 bg-gray-50 rounded-2xl flex items-center justify-center text-gray-300">
                                            <DocumentCheckIcon class="h-8 w-8" />
                                        </div>
                                        <div>
                                            <p class="font-black text-gray-900 text-base">Aucun certificat trouvé</p>
                                            <p class="text-xs text-gray-400 font-medium mt-1">Aucune demande ne correspond à vos critères de recherche.</p>
                                        </div>
                                        <button
                                            v-if="hasActiveFilters"
                                            @click="clearFilters"
                                            class="px-4 py-2 bg-blue-600 text-white rounded-xl text-xs font-bold hover:bg-blue-700 transition-colors"
                                        >
                                            Réinitialiser la recherche
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="certificates.links && certificates.links.length > 3" class="px-8 py-5 border-t border-gray-100 bg-gray-50/40 flex items-center justify-between">
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">
                        Page {{ certificates.current_page }} sur {{ certificates.last_page }} ({{ certificates.total }} résultats)
                    </span>
                    <div class="flex items-center gap-1">
                        <Component
                            v-for="(link, key) in certificates.links"
                            :key="key"
                            :is="link.url ? Link : 'span'"
                            :href="link.url"
                            v-html="link.label"
                            class="px-3.5 py-2 rounded-xl text-xs font-bold transition-all"
                            :class="{
                                'bg-blue-600 text-white shadow-md shadow-blue-200': link.active,
                                'text-gray-600 hover:bg-gray-100': link.url && !link.active,
                                'text-gray-300 cursor-not-allowed': !link.url
                            }"
                        />
                    </div>
                </div>
            </div>

        </div>
    </AuthenticatedLayout>
</template>
