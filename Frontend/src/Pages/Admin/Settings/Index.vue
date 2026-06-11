<script setup>
import { reactive } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { 
    Cog6ToothIcon, 
    BuildingLibraryIcon, 
    ClockIcon,
    CheckCircleIcon,
    DocumentTextIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    settings: Object,
    update_url: String,
});

// Prepare the form data structure from grouped settings
const initialData = [];
Object.values(props.settings).forEach(group => {
    group.forEach(setting => {
        initialData.push({ key: setting.key, value: setting.value });
    });
});

const form = useForm({
    settings: initialData
});

const submit = () => {
    form.post(props.update_url);
};

const getIcon = (group) => {
    switch (group.toLowerCase()) {
        case 'institutionnel': return BuildingLibraryIcon;
        case 'opérationnel': return ClockIcon;
        default: return DocumentTextIcon;
    }
};
</script>

<template>
    <Head title="Paramètres Généraux" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <div class="p-2 bg-purple-600 rounded-lg shadow-lg">
                    <Cog6ToothIcon class="h-6 w-6 text-white" />
                </div>
                <div>
                    <h2 class="font-black text-2xl text-gray-900 tracking-tight">Paramètres Généraux</h2>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">
                        Configuration institutionnelle et opérationnelle
                    </p>
                </div>
            </div>
        </template>

        <div class="max-w-4xl mx-auto pb-20 mt-8">
            <form @submit.prevent="submit" class="space-y-8">
                
                <div v-for="(groupSettings, groupName) in settings" :key="groupName" 
                     class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8">
                    
                    <h3 class="text-xs font-black text-blue-600 uppercase tracking-widest mb-8 flex items-center gap-3">
                        <component :is="getIcon(groupName)" class="h-5 w-5" />
                        Domaine : {{ groupName }}
                    </h3>

                    <div class="space-y-8">
                        <div v-for="setting in groupSettings" :key="setting.id" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-center">
                            <div class="md:col-span-1">
                                <label class="block text-sm font-black text-gray-900 leading-tight">{{ setting.display_name }}</label>
                                <span class="text-[9px] font-bold text-gray-400 uppercase tracking-tighter">{{ setting.key }}</span>
                            </div>
                            <div class="md:col-span-2">
                                <input 
                                    v-model="form.settings.find(s => s.key === setting.key).value"
                                    :type="setting.key.includes('time') ? 'time' : 'text'"
                                    class="w-full px-6 py-4 rounded-2xl border-gray-100 bg-gray-50 focus:bg-white focus:ring-blue-600 font-bold transition-all"
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end pt-4">
                    <button type="submit" :disabled="form.processing"
                            class="inline-flex items-center px-12 py-5 bg-gray-900 text-white rounded-2xl font-black text-xs uppercase shadow-2xl shadow-gray-200 hover:bg-black transition-all active:scale-95 disabled:bg-gray-400">
                        <CheckCircleIcon class="w-5 h-5 mr-3 stroke-[3]" />
                        {{ form.processing ? 'Mise à jour en cours...' : 'Sauvegarder les configurations' }}
                    </button>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
