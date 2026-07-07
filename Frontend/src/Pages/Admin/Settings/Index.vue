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

// Generate 24h format time options by 15 minutes steps
const timeOptions = [];
for (let h = 0; h < 24; h++) {
    const hh = String(h).padStart(2, '0');
    timeOptions.push(`${hh}:00`);
    timeOptions.push(`${hh}:15`);
    timeOptions.push(`${hh}:30`);
    timeOptions.push(`${hh}:45`);
}
</script>

<template>
    <Head title="Paramètres Généraux" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <div class="p-2 rounded-lg shadow-lg" style="background-color: #1E690F;">
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
                    
                    <h3 class="text-xs font-black uppercase tracking-widest mb-8 flex items-center gap-3" style="color: #1E690F;">
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
                                <select 
                                    v-if="setting.key.includes('time')"
                                    v-model="form.settings.find(s => s.key === setting.key).value"
                                    class="w-full px-6 py-4 rounded-2xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold transition-all appearance-none cursor-pointer"
                                    style="background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%231E690F%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-5%200-9.3%201.8-12.9%205.4A17.6%2017.6%200%200%200%200%2082.2c0%205%201.8%209.3%205.4%2012.9l128%20127.9c3.6%203.6%207.8%205.4%2012.8%205.4s9.2-1.8%2012.8-5.4L287%2095c3.5-3.5%205.4-7.8%205.4-12.8%200-5-1.9-9.2-5.5-12.8z%22%2F%3E%3C%2Fsvg%3E'); background-repeat: no-repeat; background-position: right 1.5rem top 50%; background-size: 0.65rem auto; padding-right: 3rem;"
                                >
                                    <option v-for="time in timeOptions" :key="time" :value="time">
                                        {{ time }}
                                    </option>
                                </select>
                                <input 
                                    v-else
                                    v-model="form.settings.find(s => s.key === setting.key).value"
                                    type="text"
                                    class="w-full px-6 py-4 rounded-2xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold transition-all"
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end pt-4">
                    <button type="submit" :disabled="form.processing"
                            class="inline-flex items-center px-12 py-5 text-white rounded-2xl font-black text-xs uppercase shadow-2xl shadow-green-100 hover:bg-[#185709] transition-all active:scale-95 disabled:bg-gray-400"
                            style="background-color: #1E690F;">
                        <CheckCircleIcon class="w-5 h-5 mr-3 stroke-[3]" />
                        {{ form.processing ? 'Mise à jour en cours...' : 'Sauvegarder les configurations' }}
                    </button>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
