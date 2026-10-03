<script setup>
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { 
    PlusIcon, 
    BookOpenIcon,
    CalendarIcon,
    BuildingLibraryIcon,
    CheckCircleIcon,
    XCircleIcon,
    LockClosedIcon,
    LockOpenIcon,
    PencilIcon,
    TrashIcon,
    MagnifyingGlassIcon,
    XMarkIcon,
    ArrowsUpDownIcon,
    BarsArrowDownIcon,
    BarsArrowUpIcon,
    ArrowPathIcon,
    FolderOpenIcon,
    RectangleStackIcon,
    FunnelIcon,
    DocumentTextIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    registries: Object,
    availableYears: Array,
    availableNumbers: Array,
    filters: Object,
});

const isSuperviseur = computed(() => usePage().props.auth.user?.role === 'Superviseur');
const isAdmin = computed(() => usePage().props.auth.user?.role === 'Administrateur technique');

// Filter & Sort state
const search = ref(props.filters?.search || '');
const typeFilter = ref(props.filters?.type || 'all');
const yearFilter = ref(props.filters?.year || 'all');
const numberFilter = ref(props.filters?.number || 'all');
const statusFilter = ref(props.filters?.status || 'all');
const sortBy = ref(props.filters?.sort_by || 'year');
const sortOrder = ref(props.filters?.sort_order || 'desc');

let searchTimeout = null;
const triggerSearch = () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        applyFilters();
    }, 350);
};

watch(search, () => triggerSearch());

const applyFilters = () => {
    const params = {};
    if (search.value.trim()) params.search = search.value.trim();
    if (typeFilter.value && typeFilter.value !== 'all') params.type = typeFilter.value;
    if (yearFilter.value && yearFilter.value !== 'all') params.year = yearFilter.value;
    if (numberFilter.value && numberFilter.value !== 'all') params.number = numberFilter.value;
    if (statusFilter.value && statusFilter.value !== 'all') params.status = statusFilter.value;
    if (sortBy.value) params.sort_by = sortBy.value;
    if (sortOrder.value) params.sort_order = sortOrder.value;

    router.get('/admin/registries', params, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const toggleSortOrder = () => {
    sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc';
    applyFilters();
};

const clearSearch = () => {
    search.value = '';
    applyFilters();
};

const resetFilters = () => {
    search.value = '';
    typeFilter.value = 'all';
    yearFilter.value = 'all';
    numberFilter.value = 'all';
    statusFilter.value = 'all';
    sortBy.value = 'year';
    sortOrder.value = 'desc';
    applyFilters();
};

const hasActiveFilters = computed(() => {
    return Boolean(
        search.value.trim() ||
        (typeFilter.value && typeFilter.value !== 'all') ||
        (yearFilter.value && yearFilter.value !== 'all') ||
        (numberFilter.value && numberFilter.value !== 'all') ||
        (statusFilter.value && statusFilter.value !== 'all') ||
        sortBy.value !== 'year' ||
        sortOrder.value !== 'desc'
    );
});

const showConfirmModal = ref(false);
const modalType = ref(''); // 'close' or 'reopen' or 'delete'
const activeRegistry = ref(null);

const closeRegistry = (registry) => {
    activeRegistry.value = registry;
    modalType.value = 'close';
    showConfirmModal.value = true;
};

const currentYear = new Date().getFullYear();

const reopenRegistry = (registry) => {
    activeRegistry.value = registry;
    modalType.value = 'reopen';
    showConfirmModal.value = true;
};

const deleteRegistry = (registry) => {
    activeRegistry.value = registry;
    modalType.value = 'delete';
    showConfirmModal.value = true;
};

const executeAction = () => {
    showConfirmModal.value = false;
    if (!activeRegistry.value) return;

    if (modalType.value === 'close') {
        router.post(`/admin/registries/${activeRegistry.value.id}/close`, {}, {
            onFinish: () => {
                activeRegistry.value = null;
            }
        });
    } else if (modalType.value === 'reopen') {
        router.post(`/admin/registries/${activeRegistry.value.id}/reopen`, {}, {
            onFinish: () => {
                activeRegistry.value = null;
            }
        });
    } else if (modalType.value === 'delete') {
        router.delete(`/admin/registries/${activeRegistry.value.id}`, {
            onFinish: () => {
                activeRegistry.value = null;
            }
        });
    }
};

const cancelAction = () => {
    showConfirmModal.value = false;
    activeRegistry.value = null;
};

const getStatusColor = (status) => {
    return status === 'open' ? 'text-green-600 bg-green-50' : 'text-gray-600 bg-gray-50';
};

const getActsCount = (reg) => {
    if (reg.birth_acts_count !== undefined) {
        return (reg.birth_acts_count || 0) + (reg.marriage_acts_count || 0) + (reg.death_acts_count || 0);
    }
    return reg.acts_count || 0;
};
</script>

<template>
    <Head title="Gestion des Registres" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="p-2.5 bg-blue-600 rounded-2xl shadow-lg shadow-blue-200">
                        <BookOpenIcon class="h-6 w-6 text-white" />
                    </div>
                    <div>
                        <h2 class="font-black text-2xl text-gray-900 tracking-tight">Registres État-Civil</h2>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">
                            Ouverture, suivi et vérification des volumes annuels
                        </p>
                    </div>
                </div>

                <Link
                    :href="`/admin/registries/create`"
                    class="inline-flex items-center justify-center px-6 py-3 bg-blue-600 border border-transparent rounded-xl font-black text-xs text-white uppercase tracking-widest hover:bg-blue-700 shadow-xl shadow-blue-100 transition-all active:scale-95"
                >
                    <PlusIcon class="w-4 h-4 mr-2 stroke-[3]" />
                    Ouvrir un registre
                </Link>
            </div>
        </template>

        <div class="space-y-6">

            <!-- ── Filtres et Tri Avancés ─────────────────────────────── -->
            <div class="bg-white rounded-3xl p-5 shadow-sm border border-gray-100 space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3.5">

                    <!-- Recherche -->
                    <div class="lg:col-span-2">
                        <label class="block text-[10px] font-black uppercase tracking-wider text-gray-400 mb-1">
                            Recherche
                        </label>
                        <div class="relative">
                            <MagnifyingGlassIcon class="absolute left-3.5 inset-y-0 my-auto h-4 w-4 text-gray-400 pointer-events-none" />
                            <input
                                v-model="search"
                                type="text"
                                placeholder="Préfixe, code ou nom du centre..."
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

                    <!-- Filtre Type -->
                    <div>
                        <label class="block text-[10px] font-black uppercase tracking-wider text-gray-400 mb-1">
                            Type d'Acte
                        </label>
                        <div class="relative">
                            <select
                                v-model="typeFilter"
                                @change="applyFilters"
                                class="w-full px-3 py-2.5 bg-gray-50/80 border border-gray-200 rounded-xl text-xs font-bold text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:bg-white transition-all appearance-none cursor-pointer"
                            >
                                <option value="all">Tous les types</option>
                                <option value="naissance">Naissance</option>
                                <option value="mariage">Mariage</option>
                                <option value="deces">Décès</option>
                                <option value="divers">Divers</option>
                            </select>
                            <ChevronDownIcon class="absolute right-3 inset-y-0 my-auto h-4 w-4 text-gray-400 pointer-events-none" />
                        </div>
                    </div>

                    <!-- Filtre Année (Année par année) -->
                    <div>
                        <label class="block text-[10px] font-black uppercase tracking-wider text-gray-400 mb-1">
                            Année
                        </label>
                        <div class="relative">
                            <CalendarIcon class="absolute left-3 inset-y-0 my-auto h-4 w-4 text-gray-400 pointer-events-none" />
                            <select
                                v-model="yearFilter"
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

                    <!-- Filtre Numéro de Volume (Numéro par numéro) -->
                    <div>
                        <label class="block text-[10px] font-black uppercase tracking-wider text-gray-400 mb-1">
                            N° de Volume
                        </label>
                        <div class="relative">
                            <RectangleStackIcon class="absolute left-3 inset-y-0 my-auto h-4 w-4 text-gray-400 pointer-events-none" />
                            <select
                                v-model="numberFilter"
                                @change="applyFilters"
                                class="w-full pl-9 pr-8 py-2.5 bg-gray-50/80 border border-gray-200 rounded-xl text-xs font-bold text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:bg-white transition-all appearance-none cursor-pointer"
                            >
                                <option value="all">Tous les volumes</option>
                                <option v-for="num in availableNumbers" :key="num" :value="num">
                                    Volume {{ num }}
                                </option>
                            </select>
                            <ChevronDownIcon class="absolute right-3 inset-y-0 my-auto h-4 w-4 text-gray-400 pointer-events-none" />
                        </div>
                    </div>

                    <!-- Tri -->
                    <div>
                        <label class="block text-[10px] font-black uppercase tracking-wider text-gray-400 mb-1">
                            Trier par
                        </label>
                        <div class="flex items-center gap-1.5">
                            <div class="relative flex-1">
                                <select
                                    v-model="sortBy"
                                    @change="applyFilters"
                                    class="w-full pl-3 pr-7 py-2.5 bg-gray-50/80 border border-gray-200 rounded-xl text-xs font-bold text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:bg-white transition-all appearance-none cursor-pointer"
                                >
                                    <option value="year">Année</option>
                                    <option value="number">N° Volume</option>
                                    <option value="type">Type</option>
                                    <option value="status">Statut</option>
                                </select>
                                <ChevronDownIcon class="absolute right-2.5 inset-y-0 my-auto h-3.5 w-3.5 text-gray-400 pointer-events-none" />
                            </div>

                            <button
                                type="button"
                                @click="toggleSortOrder"
                                :title="sortOrder === 'asc' ? 'Croissant. Cliquer pour inverser.' : 'Décroissant. Cliquer pour inverser.'"
                                class="p-2.5 bg-gray-50 border border-gray-200 rounded-xl text-blue-600 hover:bg-blue-50 transition-all flex items-center justify-center shadow-sm"
                            >
                                <BarsArrowUpIcon v-if="sortOrder === 'asc'" class="h-4 w-4" />
                                <BarsArrowDownIcon v-else class="h-4 w-4" />
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Footer filtres -->
                <div class="pt-3 border-t border-gray-100 flex items-center justify-between text-xs">
                    <span class="text-gray-500 font-medium">
                        <strong class="text-gray-900 font-black">{{ registries.total }}</strong> registre(s) répertorié(s)
                    </span>

                    <button
                        v-if="hasActiveFilters"
                        type="button"
                        @click="resetFilters"
                        class="inline-flex items-center gap-1.5 px-3 py-1 bg-rose-50 text-rose-700 hover:bg-rose-100 rounded-lg font-bold text-[11px] transition-all"
                    >
                        <ArrowPathIcon class="h-3 w-3" />
                        Réinitialiser filtres & tri
                    </button>
                </div>
            </div>

            <!-- ── Grille des Registres ───────────────────────────────── -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div v-for="registry in registries.data" :key="registry.id" 
                     class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100 hover:shadow-xl hover:shadow-blue-50 transition-all group overflow-hidden relative flex flex-col justify-between">
                    
                    <div>
                        <!-- Status Badge -->
                        <div class="absolute top-4 right-4">
                            <span :class="getStatusColor(registry.status)" class="flex items-center gap-1 text-[9px] font-black uppercase px-2.5 py-1 rounded-full border border-current opacity-90">
                                <component :is="registry.status === 'open' ? CheckCircleIcon : LockClosedIcon" class="h-3 w-3" />
                                {{ registry.status === 'open' ? 'Ouvert' : 'Clôturé' }}
                            </span>
                        </div>

                        <div class="flex items-start gap-4 mb-5">
                            <div class="h-12 w-12 rounded-2xl flex items-center justify-center shadow-sm"
                                :class="{
                                    'text-blue-600 bg-blue-50': registry.type === 'naissance',
                                    'text-pink-600 bg-pink-50': registry.type === 'mariage',
                                    'text-gray-600 bg-gray-50': registry.type === 'deces',
                                    'text-purple-600 bg-purple-50': !['naissance','mariage','deces'].includes(registry.type)
                                }"
                            >
                                <BookOpenIcon class="h-6 w-6" />
                            </div>
                            <div>
                                <h3 class="text-base font-black text-gray-900 leading-tight capitalize">
                                    Volume {{ registry.number }} — {{ registry.type.replace('_', ' ') }}s
                                </h3>
                                <p class="text-[10px] font-black text-blue-600 font-mono tracking-wider mt-0.5">{{ registry.reference_prefix }}</p>
                            </div>
                        </div>

                        <div class="space-y-2.5 mb-6 bg-gray-50/70 p-4 rounded-2xl">
                            <div class="flex items-center justify-between text-xs font-bold text-gray-700">
                                <div class="flex items-center gap-2">
                                    <CalendarIcon class="h-4 w-4 text-gray-400" />
                                    <span>Année du registre :</span>
                                </div>
                                <span class="px-2 py-0.5 bg-white border border-gray-200 rounded-md font-black">{{ registry.year }}</span>
                            </div>
                            <div class="flex items-center justify-between text-xs font-bold text-gray-700">
                                <div class="flex items-center gap-2">
                                    <RectangleStackIcon class="h-4 w-4 text-gray-400" />
                                    <span>Numéro de volume :</span>
                                </div>
                                <span class="px-2 py-0.5 bg-white border border-gray-200 rounded-md font-black">Vol. {{ registry.number }}</span>
                            </div>
                            <div class="flex items-center justify-between text-xs font-bold text-gray-700">
                                <div class="flex items-center gap-2">
                                    <BuildingLibraryIcon class="h-4 w-4 text-gray-400" />
                                    <span>Centre rattaché :</span>
                                </div>
                                <span class="truncate max-w-[150px] font-medium text-gray-600">{{ registry.center?.name || 'Centre Inconnu' }}</span>
                            </div>
                            <div class="flex items-center justify-between text-xs font-bold text-gray-700">
                                <div class="flex items-center gap-2">
                                    <DocumentTextIcon class="h-4 w-4 text-gray-400" />
                                    <span>Actes enregistrés :</span>
                                </div>
                                <span class="text-blue-700 font-black">{{ getActsCount(registry) }} / 50</span>
                            </div>
                            <div v-if="registry.opening_date" class="text-[10px] text-gray-400 pt-1 border-t border-gray-200/50">
                                Ouvert le : {{ new Date(registry.opening_date).toLocaleDateString('fr-FR') }}
                            </div>
                        </div>
                    </div>

                    <div>
                        <!-- Lien vers la consultation et saisie des actes de ce registre -->
                        <div v-if="['naissance', 'mariage', 'deces'].includes(registry.type)" class="mb-3 flex items-center gap-2">
                            <Link
                                :href="`/acts/${registry.type}/list?registry_id=${registry.id}&sort_by=number&sort_order=asc`"
                                class="flex-1 py-2 px-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-black uppercase tracking-wider flex items-center justify-center gap-2 transition-all shadow-md shadow-blue-100"
                            >
                                <FolderOpenIcon class="h-4 w-4" />
                                <span>Vérifier les actes</span>
                            </Link>
                            <Link
                                v-if="registry.status === 'open'"
                                :href="`/acts/${registry.type}/create?old_registry=1&registry_id=${registry.id}`"
                                class="py-2 px-3 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-xs font-black uppercase tracking-wider flex items-center justify-center gap-1.5 transition-all shadow-md shadow-amber-100"
                                title="Saisir un acte dans ce registre"
                            >
                                <PlusIcon class="h-4 w-4 stroke-[3]" />
                                <span>Saisir</span>
                            </Link>
                        </div>

                        <!-- Admin Edit/Delete Actions -->
                        <div v-if="isAdmin" class="flex items-center gap-2 mb-3 pt-3 border-t border-gray-100">
                            <Link 
                                :href="`/admin/registries/${registry.id}/edit`"
                                class="flex-1 py-1.5 bg-blue-50 text-blue-700 hover:bg-blue-100 rounded-xl transition-all text-[10px] font-black uppercase tracking-widest flex items-center justify-center gap-1.5"
                            >
                                <PencilIcon class="h-3.5 w-3.5" />
                                Modifier
                            </Link>
                            <button 
                                @click="deleteRegistry(registry)"
                                class="flex-1 py-1.5 bg-red-50 text-red-700 hover:bg-red-100 rounded-xl transition-all text-[10px] font-black uppercase tracking-widest flex items-center justify-center gap-1.5"
                            >
                                <TrashIcon class="h-3.5 w-3.5" />
                                Supprimer
                            </button>
                        </div>

                        <div class="flex items-center justify-end gap-2 pt-3 border-t border-gray-100">
                            <template v-if="!isSuperviseur">
                                <button 
                                    v-if="registry.status === 'open'"
                                    @click="closeRegistry(registry)" 
                                    class="w-full py-2 bg-gray-50 text-gray-500 hover:bg-red-50 hover:text-red-700 rounded-xl transition-all text-[10px] font-black uppercase tracking-widest flex items-center justify-center gap-2"
                                >
                                    <LockClosedIcon class="h-4 w-4" />
                                    Clôturer le registre
                                </button>
                                <div v-else class="w-full flex flex-col gap-2">
                                    <div class="w-full py-1.5 bg-gray-50 text-gray-400 rounded-xl text-[10px] font-bold uppercase tracking-wider text-center italic">
                                        Clôturé {{ registry.closing_date ? 'le ' + new Date(registry.closing_date).toLocaleDateString('fr-FR') : '' }}
                                    </div>
                                    <button
                                        v-if="registry.year === currentYear"
                                        @click="reopenRegistry(registry)"
                                        class="w-full py-1.5 bg-orange-50 text-orange-600 hover:bg-orange-100 rounded-xl transition-all text-[10px] font-black uppercase tracking-widest flex items-center justify-center gap-2"
                                    >
                                        <BookOpenIcon class="h-4 w-4" />
                                        Réouvrir le registre
                                    </button>
                                </div>
                            </template>
                            <template v-else>
                                <div class="w-full py-2 bg-gray-50 text-gray-400 rounded-xl text-[10px] font-black uppercase tracking-widest text-center italic">
                                    <span v-if="registry.status === 'open'">Registre Actif</span>
                                    <span v-else>Clôturé {{ registry.closing_date ? 'le ' + new Date(registry.closing_date).toLocaleDateString('fr-FR') : '' }}</span>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <div v-if="registries.data.length === 0" class="col-span-full py-20 text-center bg-white rounded-3xl border border-gray-100">
                     <div class="flex flex-col items-center gap-3 max-w-sm mx-auto">
                        <BookOpenIcon class="h-12 w-12 text-gray-300" />
                        <p class="text-sm font-black text-gray-700">Aucun registre trouvé</p>
                        <p class="text-xs text-gray-400 font-medium">
                            Aucun registre ne correspond à vos critères de filtrage actuels.
                        </p>
                        <button
                            v-if="hasActiveFilters"
                            type="button"
                            @click="resetFilters"
                            class="mt-2 text-xs font-black uppercase text-blue-600 hover:text-blue-800 tracking-wider"
                        >
                            Réinitialiser les filtres
                        </button>
                     </div>
                </div>
            </div>

            <!-- Pagination Inertia -->
            <div v-if="registries.links && registries.links.length > 3" class="px-8 py-5 border border-gray-100 bg-white rounded-2xl flex flex-col sm:flex-row items-center justify-between gap-4">
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">
                    Page {{ registries.current_page }} sur {{ registries.last_page }} ({{ registries.total }} registres)
                </span>
                <div class="flex items-center gap-1 overflow-x-auto">
                    <Component
                        v-for="(link, key) in registries.links"
                        :key="key"
                        :is="link.url ? Link : 'span'"
                        :href="link.url"
                        v-html="link.label"
                        class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all"
                        :class="{
                            'bg-blue-600 text-white shadow-md shadow-blue-200': link.active,
                            'text-gray-600 hover:bg-gray-100': link.url && !link.active,
                            'text-gray-300 cursor-not-allowed': !link.url
                        }"
                    />
                </div>
            </div>
        </div>

        <!-- Custom Confirm Action Modal -->
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
                  <div class="flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-2xl"
                    :class="{
                      'bg-red-50 text-red-600': modalType === 'close' || modalType === 'delete',
                      'bg-orange-50 text-orange-600': modalType === 'reopen'
                    }"
                  >
                    <component :is="modalType === 'close' ? LockClosedIcon : (modalType === 'delete' ? TrashIcon : BookOpenIcon)" class="h-6 w-6" />
                  </div>
                  <div class="flex-1 min-w-0">
                    <h3 class="text-lg font-black text-gray-900 leading-tight">
                      {{ modalType === 'close' ? 'Clôture du registre' : (modalType === 'delete' ? 'Suppression du registre' : 'Réouverture du registre') }}
                    </h3>
                    <p class="mt-2 text-sm text-gray-500 font-medium">
                      <span v-if="modalType === 'close'">
                        Voulez-vous vraiment clôturer le volume {{ activeRegistry?.number }} du registre des <strong class="capitalize">{{ activeRegistry?.type }}s</strong> pour l'année <strong>{{ activeRegistry?.year }}</strong> ? Plus aucun nouvel acte ne pourra y être ajouté après cette opération.
                      </span>
                      <span v-else-if="modalType === 'delete'">
                        Voulez-vous vraiment supprimer définitivement le volume {{ activeRegistry?.number }} du registre des <strong class="capitalize">{{ activeRegistry?.type }}s</strong> pour l'année <strong>{{ activeRegistry?.year }}</strong> ? Cette action est irréversible et ne peut être effectuée que si le registre ne contient aucun acte.
                      </span>
                      <span v-else>
                        Voulez-vous vraiment réouvrir le volume {{ activeRegistry?.number }} du registre des <strong class="capitalize">{{ activeRegistry?.type }}s</strong> pour l'année <strong>{{ activeRegistry?.year }}</strong> ? Les officiers pourront à nouveau y enregistrer des actes.
                      </span>
                    </p>
                    
                    <div class="mt-8 flex justify-end gap-3">
                      <button
                        type="button"
                        @click="cancelAction"
                        class="px-6 py-3 bg-white border border-gray-200 rounded-xl font-black text-[10px] text-gray-500 uppercase tracking-widest hover:bg-gray-50 transition-all active:scale-95"
                      >
                        Annuler
                      </button>
                      <button
                        type="button"
                        @click="executeAction"
                        class="px-6 py-3 rounded-xl font-black text-[10px] uppercase tracking-widest transition-all active:scale-95 text-white"
                        :class="{
                          'bg-red-600 hover:bg-red-700 shadow-xl shadow-red-100': modalType === 'close' || modalType === 'delete',
                          'bg-orange-600 hover:bg-orange-700 shadow-xl shadow-orange-100': modalType === 'reopen'
                        }"
                      >
                        {{ modalType === 'close' ? 'Clôturer' : (modalType === 'delete' ? 'Supprimer' : 'Réouvrir') }}
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
