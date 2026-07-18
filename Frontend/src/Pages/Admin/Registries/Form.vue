<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { 
    ArrowLeftIcon,
    CheckCircleIcon,
    BookOpenIcon
} from '@heroicons/vue/24/outline';
import { computed, watch } from 'vue';

const props = defineProps({
    centers: Array,
    types: Array,
});

const form = useForm({
    civil_registration_center_id: '',
    type: 'naissance',
    year: new Date().getFullYear(),
    number: 1,
    reference_prefix: '',
    status: 'open',
    opening_date: new Date().toISOString().split('T')[0],
});

// Auto-generate prefix
watch([() => form.type, () => form.civil_registration_center_id, () => form.year, () => form.number], () => {
    const center = props.centers.find(c => c.id === form.civil_registration_center_id);
    if (center && form.type && form.year) {
        const typeInitial = form.type.charAt(0).toUpperCase();
        const numSuffix = form.number > 1 ? `-${form.number}` : '';
        form.reference_prefix = `${typeInitial}-${form.year}-${center.code}${numSuffix}`;
    }
}, { immediate: true });

const submit = () => {
    form.post(`/admin/registries`);
};
</script>

<template>
    <Head title="Ouvrir un Registre" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <Link :href="`/admin/registries`" class="p-2 hover:bg-gray-100 rounded-full transition-colors">
                    <ArrowLeftIcon class="h-5 w-5 text-gray-500" />
                </Link>
                <div>
                    <h2 class="font-black text-2xl text-gray-900 tracking-tight">Ouvrir un nouveau registre</h2>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Configuration initiale du volume</p>
                </div>
            </div>
        </template>

        <div class="max-w-2xl mx-auto mt-12 bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-8 py-4" style="background-color: #1E690F;">
                <div class="flex items-center gap-2 text-white/80 text-[10px] font-black uppercase tracking-widest">
                    <BookOpenIcon class="h-4 w-4" />
                    Détails du Registre
                </div>
            </div>

            <form @submit.prevent="submit" class="p-8 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Center Selection -->
                    <div class="col-span-full">
                        <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-2 ml-1">Centre d'État Civil</label>
                        <select 
                            v-model="form.civil_registration_center_id"
                            class="w-full px-5 py-4 bg-white border border-gray-300 rounded-2xl focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold text-gray-700 transition-all"
                            required
                        >
                            <option value="" disabled>Sélectionner un centre</option>
                            <option v-for="center in centers" :key="center.id" :value="center.id">
                                {{ center.name }} ({{ center.code }})
                            </option>
                        </select>
                        <div v-if="form.errors.civil_registration_center_id" class="text-xs text-red-500 mt-1 ml-1 font-bold">{{ form.errors.civil_registration_center_id }}</div>
                    </div>

                    <!-- Type Selection -->
                    <div>
                        <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-2 ml-1">Type d'acte</label>
                        <select 
                            v-model="form.type"
                            class="w-full px-5 py-4 bg-white border border-gray-300 rounded-2xl focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold text-gray-700 transition-all capitalize"
                            required
                        >
                            <option v-for="t in types" :key="t" :value="t">{{ t }}</option>
                        </select>
                        <div v-if="form.errors.type" class="text-xs text-red-500 mt-1 ml-1 font-bold">{{ form.errors.type }}</div>
                    </div>

                    <!-- Year -->
                    <div>
                        <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-2 ml-1">Année</label>
                        <input 
                            type="number"
                            v-model="form.year"
                            class="w-full px-5 py-4 bg-white border border-gray-300 rounded-2xl focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold text-gray-700 transition-all"
                            required
                        />
                    </div>

                    <!-- Registry Number / Volume -->
                    <div>
                        <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-2 ml-1">Numéro de volume / Discriminant</label>
                        <input 
                            type="number"
                            min="1"
                            v-model="form.number"
                            class="w-full px-5 py-4 bg-white border border-gray-300 rounded-2xl focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold text-gray-700 transition-all"
                            required
                        />
                        <div v-if="form.errors.number" class="text-xs text-red-500 mt-1 ml-1 font-bold">{{ form.errors.number }}</div>
                    </div>

                    <!-- Reference Prefix -->
                    <div class="col-span-full">
                        <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-2 ml-1">Préfixe de Référence</label>
                        <input 
                            type="text"
                            v-model="form.reference_prefix"
                            placeholder="Ex: N-2026-KOL"
                            class="w-full px-5 py-4 bg-white border border-gray-300 rounded-2xl focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold text-gray-700 transition-all uppercase"
                            required
                        />
                        <div v-if="form.errors.reference_prefix" class="text-xs text-red-500 mt-2 ml-1 font-bold">{{ form.errors.reference_prefix }}</div>
                        <p v-else class="text-[9px] text-gray-400 mt-2 ml-1 italic">Ce préfixe sera utilisé pour générer les numéros de référence des actes.</p>
                    </div>

                    <!-- Opening Date -->
                    <div>
                        <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-2 ml-1">Date d'ouverture</label>
                        <input 
                            type="date"
                            v-model="form.opening_date"
                            class="w-full px-5 py-4 bg-white border border-gray-300 rounded-2xl focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold text-gray-700 transition-all"
                        />
                    </div>
                </div>

                <div class="pt-6">
                    <button 
                        type="submit"
                        :disabled="form.processing"
                        class="w-full flex items-center justify-center gap-3 px-8 py-5 text-white rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-[#185709] shadow-xl shadow-green-100 transition-all active:scale-95 disabled:opacity-50"
                        style="background-color: #1E690F;"
                    >
                        <CheckCircleIcon class="h-5 w-5 stroke-[2.5]" />
                        Confirmer l'ouverture du registre
                    </button>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
