<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { 
    PlusIcon, 
    BookOpenIcon,
    CalendarIcon,
    BuildingLibraryIcon,
    CheckCircleIcon,
    XCircleIcon,
    LockClosedIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    registries: Object,
});

const closeRegistry = (registry) => {
    if (confirm(`Voulez-vous vraiment clôturer le registre des ${registry.type}s pour l'année ${registry.year} ?`)) {
        router.post(`/admin/registries/${registry.id}/close`);
    }
};

const currentYear = new Date().getFullYear();

const reopenRegistry = (registry) => {
    if (confirm(`Voulez-vous réouvrir le registre des ${registry.type}s (${registry.year}) ?`)) {
        router.post(`/admin/registries/${registry.id}/reopen`);
    }
};

const getStatusColor = (status) => {
    return status === 'open' ? 'text-green-600 bg-green-50' : 'text-gray-600 bg-gray-50';
};
</script>

<template>
    <Head title="Gestion des Registres" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-blue-600 rounded-lg shadow-lg">
                        <BookOpenIcon class="h-6 w-6 text-white" />
                    </div>
                    <div>
                        <h2 class="font-black text-2xl text-gray-900 tracking-tight">Registres État-Civil</h2>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">
                            Ouverture et suivi des volumes annuels
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

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mt-8">
            <div v-for="registry in registries.data" :key="registry.id" 
                 class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100 hover:shadow-xl hover:shadow-blue-50 transition-all group overflow-hidden relative">
                
                <!-- Status Badge -->
                <div class="absolute top-4 right-4">
                    <span :class="getStatusColor(registry.status)" class="flex items-center gap-1 text-[9px] font-black uppercase px-2 py-1 rounded-full border border-current opacity-80">
                        <component :is="registry.status === 'open' ? CheckCircleIcon : LockClosedIcon" class="h-3 w-3" />
                        {{ registry.status === 'open' ? 'Ouvert' : 'Clôturé' }}
                    </span>
                </div>

                <div class="flex items-start gap-4 mb-6">
                    <div class="h-12 w-12 rounded-2xl flex items-center justify-center"
                        :class="{
                            'text-green-600 bg-green-50': registry.type === 'naissance',
                            'text-pink-600 bg-pink-50': registry.type === 'mariage',
                            'text-gray-600 bg-gray-50': registry.type === 'deces',
                            'text-purple-600 bg-purple-50': !['naissance','mariage','deces'].includes(registry.type)
                        }"
                    >
                        <BookOpenIcon class="h-6 w-6" />
                    </div>
                    <div>
                        <h3 class="text-lg font-black text-gray-900 leading-tight capitalize">Registre des {{ registry.type.replace('_', ' ') }}s</h3>
                        <p class="text-[10px] font-black text-blue-600 uppercase tracking-widest">{{ registry.reference_prefix }}</p>
                    </div>
                </div>

                <div class="space-y-3 mb-8">
                    <div class="flex items-center gap-2 text-xs font-bold text-gray-500">
                        <CalendarIcon class="h-4 w-4 text-gray-300" />
                        Année : {{ registry.year }}
                    </div>
                    <div class="flex items-center gap-2 text-xs font-bold text-gray-500">
                        <BuildingLibraryIcon class="h-4 w-4 text-gray-300" />
                        {{ registry.center?.name || 'Centre Inconnu' }}
                    </div>
                    <div v-if="registry.opening_date" class="flex items-center gap-2 text-[10px] font-bold text-gray-400">
                        Ouvert le : {{ new Date(registry.opening_date).toLocaleDateString('fr-FR') }}
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 pt-4 border-t border-gray-50">
                    <button 
                        v-if="registry.status === 'open'"
                        @click="closeRegistry(registry)" 
                        class="w-full py-2 bg-gray-50 text-gray-500 hover:bg-red-50 hover:text-red-700 rounded-xl transition-all text-[10px] font-black uppercase tracking-widest flex items-center justify-center gap-2"
                    >
                        <LockClosedIcon class="h-4 w-4" />
                        Clôturer le registre
                    </button>
                    <div v-else class="w-full flex flex-col gap-2">
                        <div class="w-full py-2 bg-gray-50 text-gray-300 rounded-xl text-[10px] font-black uppercase tracking-widest text-center italic">
                            Clôturé le {{ new Date(registry.closing_date).toLocaleDateString('fr-FR') }}
                        </div>
                        <button
                            v-if="registry.year === currentYear"
                            @click="reopenRegistry(registry)"
                            class="w-full py-2 bg-orange-50 text-orange-600 hover:bg-orange-100 rounded-xl transition-all text-[10px] font-black uppercase tracking-widest flex items-center justify-center gap-2"
                        >
                            <BookOpenIcon class="h-4 w-4" />
                            Réouvrir le registre
                        </button>
                    </div>
                </div>
            </div>

            <div v-if="registries.data.length === 0" class="col-span-full py-20 text-center bg-white rounded-3xl border-2 border-dashed border-gray-200">
                 <div class="flex flex-col items-center gap-4">
                    <BookOpenIcon class="h-12 w-12 text-gray-200" />
                    <p class="text-xs font-black text-gray-400 uppercase tracking-widest">Aucun registre ouvert pour le moment</p>
                 </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
