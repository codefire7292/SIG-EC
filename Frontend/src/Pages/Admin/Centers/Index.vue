<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
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

const deleteCenter = (deleteUrl) => {
    if (confirm('Êtes-vous sûr de vouloir supprimer ce centre ?')) {
        router.delete(deleteUrl);
    }
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
    </AuthenticatedLayout>
</template>
