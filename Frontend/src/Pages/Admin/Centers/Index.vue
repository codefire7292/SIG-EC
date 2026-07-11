<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { 
    PlusIcon, 
    MapPinIcon,
    PencilSquareIcon,
    TrashIcon,
    BuildingOffice2Icon,
    CheckCircleIcon,
    XCircleIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    centers: Object,
    create_url: String,
});

const showDeleteModal = ref(false);
const pendingDeleteUrl = ref('');

const deleteCenter = (deleteUrl) => {
    pendingDeleteUrl.value = deleteUrl;
    showDeleteModal.value = true;
};

const executeDelete = () => {
    showDeleteModal.value = false;
    if (pendingDeleteUrl.value) {
        router.delete(pendingDeleteUrl.value, {
            onFinish: () => {
                pendingDeleteUrl.value = '';
            }
        });
    }
};

const cancelDelete = () => {
    showDeleteModal.value = false;
    pendingDeleteUrl.value = '';
};
</script>

<template>
    <Head title="Gestion des Centres" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-blue-600 rounded-lg shadow-lg">
                        <MapPinIcon class="h-6 w-6 text-white" />
                    </div>
                    <div>
                        <h2 class="font-black text-2xl text-gray-900 tracking-tight">Centres d'État Civil</h2>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">
                            Cartographie et registres territoriaux
                        </p>
                    </div>
                </div>

                <Link
                    :href="create_url"
                    class="inline-flex items-center justify-center px-6 py-3 bg-blue-600 border border-transparent rounded-xl font-black text-xs text-white uppercase tracking-widest hover:bg-blue-700 shadow-xl shadow-blue-100 transition-all active:scale-95"
                >
                    <PlusIcon class="w-4 h-4 mr-2 stroke-[3]" />
                    Nouveau Centre
                </Link>
            </div>
        </template>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mt-8">
            <div v-for="center in centers.data" :key="center.id" 
                 class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100 hover:shadow-xl hover:shadow-blue-50 transition-all group overflow-hidden relative">
                
                <!-- Status Badge -->
                <div class="absolute top-4 right-4">
                    <span v-if="center.is_active" class="flex items-center gap-1 text-[9px] font-black uppercase text-green-600 bg-green-50 px-2 py-1 rounded-full">
                        <CheckCircleIcon class="h-3 w-3" />
                        Actif
                    </span>
                    <span v-else class="flex items-center gap-1 text-[9px] font-black uppercase text-red-600 bg-red-50 px-2 py-1 rounded-full">
                        <XCircleIcon class="h-3 w-3" />
                        Inactif
                    </span>
                </div>

                <div class="flex items-start gap-4 mb-6">
                    <div class="h-12 w-12 rounded-2xl bg-blue-50 flex items-center justify-center text-blue-600">
                        <BuildingOffice2Icon class="h-6 w-6" />
                    </div>
                    <div>
                        <h3 class="text-lg font-black text-gray-900 leading-tight">{{ center.name }}</h3>
                        <p class="text-[10px] font-black text-blue-600 uppercase tracking-widest">{{ center.code }}</p>
                    </div>
                </div>

                <div class="space-y-3 mb-8">
                    <div class="flex items-center gap-2 text-xs font-bold text-gray-500">
                        <MapPinIcon class="h-4 w-4 text-gray-300" />
                        {{ center.commune }}, {{ center.region }}
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 pt-4 border-t border-gray-50">
                    <Link :href="center.edit_url" class="p-2 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition-all">
                        <PencilSquareIcon class="h-5 w-5" />
                    </Link>
                    <button @click="deleteCenter(center.delete_url)" class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-xl transition-all">
                        <TrashIcon class="h-5 w-5" />
                    </button>
                </div>
            </div>

            <div v-if="centers.data.length === 0" class="col-span-full py-20 text-center bg-white rounded-3xl border-2 border-dashed border-gray-200">
                 <div class="flex flex-col items-center gap-4">
                    <MapPinIcon class="h-12 w-12 text-gray-200" />
                    <p class="text-xs font-black text-gray-400 uppercase tracking-widest">Aucun centre configuré pour le moment</p>
                 </div>
            </div>
        </div>

        <!-- Custom Delete Modal -->
        <Teleport to="body">
          <Transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
          >
            <div v-if="showDeleteModal" class="fixed inset-0 z-50 overflow-y-auto bg-gray-900/60 backdrop-blur-sm flex items-center justify-center p-4">
              <div 
                class="bg-white rounded-3xl overflow-hidden shadow-2xl transform transition-all max-w-lg w-full border border-gray-100"
              >
                <div class="p-8 flex items-start gap-6">
                  <div class="flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-2xl bg-red-50 text-red-600">
                    <TrashIcon class="h-6 w-6" />
                  </div>
                  <div class="flex-1 min-w-0">
                    <h3 class="text-lg font-black text-gray-900 leading-tight">
                      Suppression du centre
                    </h3>
                    <p class="mt-2 text-sm text-gray-500 font-medium">
                      Êtes-vous sûr de vouloir supprimer ce centre ? Cette action peut être irréversible si des registres y sont associés.
                    </p>
                    
                    <div class="mt-8 flex justify-end gap-3">
                      <button
                        type="button"
                        @click="cancelDelete"
                        class="px-6 py-3 bg-white border border-gray-200 rounded-xl font-black text-[10px] text-gray-500 uppercase tracking-widest hover:bg-gray-50 transition-all active:scale-95"
                      >
                        Annuler
                      </button>
                      <button
                        type="button"
                        @click="executeDelete"
                        class="px-6 py-3 bg-red-600 hover:bg-red-700 text-white rounded-xl font-black text-[10px] uppercase tracking-widest shadow-xl shadow-red-100 transition-all active:scale-95"
                      >
                        Supprimer
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
