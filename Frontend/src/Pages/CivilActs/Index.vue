<script setup>
import { computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { 
    PlusIcon, 
    MagnifyingGlassIcon,
    ChevronRightIcon,
    FingerPrintIcon,
    BuildingLibraryIcon,
    ShieldCheckIcon,
    ArrowUpTrayIcon,
    ArrowDownTrayIcon,
    DocumentTextIcon
} from '@heroicons/vue/24/outline';
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    acts: Object,
    type: String,
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
    if (props.type === 'naissance') return `${act.first_name} ${act.last_name}`;
    if (props.type === 'mariage') return `${act.husband_last_name} & ${act.wife_last_name}`;
    if (props.type === 'deces') return `${act.deceased_first_name} ${act.deceased_last_name}`;
    return 'N/A';
};

const formatDate = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('fr-FR', {
        day: '2-digit',
        month: 'long',
        year: 'numeric'
    });
};

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
            fileInput.value.value = '';
            pendingFile.value = null;
        },
        onError: (errors) => {
            console.error(errors);
            fileInput.value.value = '';
            pendingFile.value = null;
        }
    });
};

const cancelImport = () => {
    showConfirmModal.value = false;
    pendingFile.value = null;
    fileInput.value.value = '';
};
</script>

<template>
    <Head :title="title" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-blue-600 rounded-lg shadow-lg shadow-blue-200">
                        <component :is="icon" class="h-6 w-6 text-white" />
                    </div>
                    <div>
                        <h2 class="font-black text-2xl text-gray-900 tracking-tight">{{ title }}</h2>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">
                            Gestion des actes et registres officiels
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-3">
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
                        class="inline-flex items-center justify-center px-6 py-3 bg-white border border-gray-200 rounded-xl font-black text-xs text-green-700 uppercase tracking-widest hover:bg-green-50 shadow-sm transition-all active:scale-95"
                        download
                    >
                        <ArrowDownTrayIcon class="w-4 h-4 mr-2" />
                        Modèle Excel
                    </a>
                    <button
                        v-if="canImportExcel"
                        @click="$refs.fileInput.click()"
                        class="inline-flex items-center justify-center px-6 py-3 bg-white border border-gray-200 rounded-xl font-black text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50 shadow-sm transition-all active:scale-95"
                    >
                        <ArrowUpTrayIcon class="w-4 h-4 mr-2" />
                        Importer Excel
                    </button>
                    <Link
                        :href="`/acts/${type}/create?old_registry=1`"
                        class="inline-flex items-center justify-center px-6 py-3 bg-white border border-gray-200 rounded-xl font-black text-xs text-amber-600 uppercase tracking-widest hover:bg-amber-50 shadow-sm transition-all active:scale-95"
                    >
                        <DocumentTextIcon class="w-4 h-4 mr-2" />
                        Saisir Ancien Registre
                    </Link>
                    <Link
                        :href="`/acts/${type}/create`"
                        class="inline-flex items-center justify-center px-6 py-3 bg-blue-600 border border-transparent rounded-xl font-black text-xs text-white uppercase tracking-widest hover:bg-blue-700 shadow-xl shadow-blue-100 transition-all active:scale-95"
                    >
                        <PlusIcon class="w-4 h-4 mr-2 stroke-[3]" />
                        Nouvel Acte
                    </Link>
                </div>
            </div>
        </template>

        <div class="space-y-6">
            <!-- Stats/Highlights (Optional) -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <div class="flex items-center gap-4 text-sm font-bold text-gray-500">
                    <MagnifyingGlassIcon class="h-5 w-5" />
                    <span>Aperçu du registre actuel</span>
                </div>
            </div>

            <!-- Acts Table -->
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                <table class="min-w-full divide-y divide-gray-100">
                    <thead class="bg-gray-50/50">
                        <tr>
                            <th class="px-8 py-5 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Référence</th>
                            <th class="px-8 py-5 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Sujet / Titulaires</th>
                            <th class="px-8 py-5 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Date Évènement</th>
                            <th class="px-8 py-5 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Statut</th>
                            <th class="px-8 py-5 text-right text-[10px] font-black text-gray-400 uppercase tracking-widest">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <tr v-for="act in acts.data" :key="act.id" class="group hover:bg-blue-50/30 transition-colors">
                            <td class="px-8 py-5">
                                <span class="text-xs font-black text-blue-900 bg-blue-50 px-2 py-1 rounded">
                                    {{ act.reference_number || 'SANS RÉF' }}
                                </span>
                            </td>
                            <td class="px-8 py-5">
                                <div class="text-sm font-black text-gray-900">{{ formatName(act) }}</div>
                                <div class="text-[10px] font-bold text-gray-400 uppercase">{{ act.registry?.name || 'Registre Principal' }}</div>
                            </td>
                            <td class="px-8 py-5 text-sm text-gray-600 font-medium">
                                {{ formatDate(act.date_of_birth || act.marriage_date || act.date_of_death) }}
                            </td>
                            <td class="px-8 py-5">
                                <span class="px-3 py-1 inline-flex text-[9px] font-black uppercase rounded-full"
                                    :class="{
                                        'bg-gray-100 text-gray-500': act.status === 'brouillon',
                                        'bg-green-100 text-green-700': act.status === 'signe',
                                        'bg-blue-100 text-blue-700': act.status === 'valide_hierarchie'
                                    }"
                                >
                                    {{ act.status }}
                                </span>
                            </td>
                            <td class="px-8 py-5 text-right">
                                <Link
                                    :href="`/acts/${type}/${act.id}`"
                                    class="inline-flex items-center text-blue-600 hover:text-blue-900 font-black text-xs uppercase tracking-tighter"
                                >
                                    Détails act
                                    <ChevronRightIcon class="ml-1 h-3 w-3" />
                                </Link>
                            </td>
                        </tr>
                        <tr v-if="acts.data.length === 0">
                            <td colspan="5" class="px-8 py-20 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    <div class="h-12 w-12 bg-gray-50 rounded-full flex items-center justify-center">
                                        <component :is="icon" class="h-6 w-6 text-gray-300" />
                                    </div>
                                    <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Aucun acte enregistré dans ce registre</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Custom Confirm Modal -->
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
