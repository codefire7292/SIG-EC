<script setup>
import { computed } from 'vue';
import { useForm, Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { 
    BuildingOffice2Icon, 
    TagIcon, 
    MapIcon,
    ArrowLeftIcon,
    CheckCircleIcon,
    PowerIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    center: Object,
    is_edit: Boolean,
    back_url: String,
    store_url: String,
    update_url: String,
});

const title = computed(() => props.is_edit ? "Modifier le centre" : "Nouveau Centre d'État Civil");

const form = useForm({
    name: props.center?.name || '',
    code: props.center?.code || '',
    commune: props.center?.commune || '',
    region: props.center?.region || '',
    is_active: props.center?.is_active ?? true,
});

const submit = () => {
    if (props.is_edit) {
        form.put(props.update_url);
    } else {
        form.post(props.store_url);
    }
};
</script>

<template>
    <Head :title="title" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <Link :href="back_url" class="p-2 hover:bg-gray-100 rounded-full transition-colors">
                    <ArrowLeftIcon class="h-5 w-5 text-gray-500" />
                </Link>
                <h2 class="font-black text-2xl text-gray-900 tracking-tight">{{ title }}</h2>
            </div>
        </template>

        <div class="max-w-3xl mx-auto pb-20 mt-8">
            <form @submit.prevent="submit" class="space-y-8">
                
                <!-- Center Info -->
                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8">
                    <h3 class="text-xs font-black text-blue-600 uppercase tracking-widest mb-6 flex items-center gap-2">
                        <BuildingOffice2Icon class="h-4 w-4" />
                        Identité du Centre
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2 col-span-full">
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Nom du Centre (ex: Centre Principal de Dakar)</label>
                            <input v-model="form.name" type="text" class="w-full px-4 py-3 rounded-2xl border-gray-100 bg-gray-50 focus:bg-white focus:ring-blue-600 font-bold" required />
                        </div>
                        <div class="space-y-2">
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Code Institutionnel (Unique)</label>
                            <div class="relative">
                                <TagIcon class="absolute left-4 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400" />
                                <input v-model="form.code" type="text" placeholder="CPC-DKR" class="w-full pl-12 pr-4 py-3 rounded-2xl border-gray-100 bg-gray-50 focus:bg-white font-bold" required />
                            </div>
                        </div>
                        <div class="space-y-2">
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Statut Opérationnel</label>
                            <div class="flex items-center gap-4 pt-2">
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" v-model="form.is_active" class="sr-only peer">
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                    <span class="ml-3 text-xs font-black uppercase tracking-widest" :class="form.is_active ? 'text-blue-600' : 'text-gray-400'">
                                        {{ form.is_active ? 'Ouvert' : 'Fermé' }}
                                    </span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Location Section -->
                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8">
                    <h3 class="text-xs font-black text-purple-600 uppercase tracking-widest mb-6 flex items-center gap-2">
                        <MapIcon class="h-4 w-4" />
                        Localisation Géographique
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Commune / Ville</label>
                            <input v-model="form.commune" type="text" class="w-full px-4 py-3 rounded-2xl border-gray-100 bg-gray-50 focus:bg-white font-bold" required />
                        </div>
                        <div class="space-y-2">
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Région / Province</label>
                            <input v-model="form.region" type="text" class="w-full px-4 py-3 rounded-2xl border-gray-100 bg-gray-50 focus:bg-white font-bold" required />
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-4">
                    <Link :href="back_url" class="px-8 py-4 bg-gray-100 text-gray-500 rounded-2xl font-black text-xs uppercase hover:bg-gray-200 transition-all">Annuler</Link>
                    <button type="submit" :disabled="form.processing"
                            class="inline-flex items-center px-10 py-4 bg-blue-600 text-white rounded-2xl font-black text-xs uppercase shadow-2xl shadow-blue-200 hover:bg-blue-700 transition-all active:scale-95 disabled:bg-gray-400">
                        <CheckCircleIcon class="w-5 h-5 mr-3 stroke-[3]" />
                        {{ is_edit ? 'Mettre à jour' : 'Enregistrer le centre' }}
                    </button>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
