<script setup>
import { computed, ref } from 'vue';
import { useForm, Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { 
    CalendarIcon, 
    MapPinIcon, 
    IdentificationIcon,
    UserGroupIcon,
    PlusCircleIcon,
    DocumentArrowUpIcon,
    DocumentIcon,
    CheckCircleIcon,
    UserIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
    act: Object,
    type: String,
    is_edit: Boolean,
});

const title = computed(() => {
    if (props.is_edit) return `Modifier l'acte (${props.type})`;
    switch (props.type) {
        case 'naissance': return 'Déclaration de Naissance';
        case 'mariage': return 'Célébration de Mariage';
        case 'deces': return 'Déclaration de Décès';
        default: return 'Nouvel Acte';
    }
});

// Affichage conditionnel de la section Déclarant
const hasDeclarant = ref(
    !!(props.act?.parents_metadata?.declarant_first_name || props.act?.parents_metadata?.declarant_last_name)
);

const form = useForm({
    // Common
    officer_comments: props.act?.officer_comments || '',
    certificate_file: null,
    certificate_path: props.act?.certificate_path || null,
    
    // Naissance — Identité de l'enfant
    first_name: props.act?.first_name || '',
    last_name: props.act?.last_name || '',
    date_of_birth: props.act?.date_of_birth || '',
    time_of_birth: props.act?.time_of_birth || '',
    place_of_birth: props.act?.place_of_birth || '',
    health_facility: props.act?.health_facility || '',
    act_registration_date: props.act?.act_registration_date || '',
    gender: props.act?.gender || 'M',
    is_judgment: props.act?.is_judgment || false,
    judgment_number: props.act?.judgment_number || '',
    judgment_date: props.act?.judgment_date || '',
    judgment_court: props.act?.judgment_court || '',
    father_name: props.act?.father_name || '',
    mother_name: props.act?.mother_name || '',
    parents_metadata: props.act?.parents_metadata || {
        father_profession: '',
        father_date_of_birth: '',
        father_place_of_birth: '',
        father_domicile: '',
        mother_profession: '',
        mother_date_of_birth: '',
        mother_place_of_birth: '',
        mother_domicile: '',
        declarant_first_name: '',
        declarant_last_name: '',
        declarant_profession: '',
        declarant_address: '',
        declarant_id_number: '',
        declarant_date: '',
        declarant_judgment_ref: '',
    },
    // Naissance — Pièces justificatives PDF
    doc_cni_pere: null,
    doc_cni_mere: null,
    doc_acte_naissance: null,
    doc_cni_declarant: null,
    doc_autres: null,

    // Mariage
    husband_first_name: props.act?.husband_first_name || '',
    husband_last_name: props.act?.husband_last_name || '',
    wife_first_name: props.act?.wife_first_name || '',
    wife_last_name: props.act?.wife_last_name || '',
    marriage_date: props.act?.marriage_date || '',
    marriage_place: props.act?.marriage_place || '',
    marriage_option: props.act?.marriage_option || 'monogamie',
    matrimonial_regime: props.act?.matrimonial_regime || 'separation_biens',
    witnesses_metadata: props.act?.witnesses_metadata || {
        witness1_name: '',
        witness1_cni: '',
        witness2_name: '',
        witness2_cni: '',
        witness3_name: '',
        witness3_cni: '',
        witness4_name: '',
        witness4_cni: ''
    },

    // Deces
    deceased_first_name: props.act?.deceased_first_name || '',
    deceased_last_name: props.act?.deceased_last_name || '',
    date_of_death: props.act?.date_of_death || '',
    place_of_death: props.act?.place_of_death || '',
    cause_of_death: props.act?.cause_of_death || '',
});

const submit = () => {
    if (props.is_edit) {
        form.transform((data) => ({
            ...data,
            _method: 'PATCH'
        })).post(`/acts/${props.type}/${props.act.id}`);
    } else {
        form.post(`/acts/${props.type}`);
    }
};
</script>

<template>
    <Head :title="title" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-black text-2xl text-gray-900 tracking-tight">{{ title }}</h2>
        </template>

        <div class="max-w-4xl mx-auto pb-20">
            <form @submit.prevent="submit" class="space-y-8">
                
                <!-- Section 1: Informations de Base -->
                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8">
                    <h3 class="text-xs font-black uppercase tracking-widest mb-6 flex items-center gap-2" style="color: #1E690F;">
                        <IdentificationIcon class="h-4 w-4" />
                        Informations d'Identification
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div v-if="type === 'naissance'" class="space-y-6 col-span-full">
                            <div class="grid grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1 pl-1">Prénoms de l'enfant</label>
                                    <input v-model="form.first_name" type="text" class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold transition-all" required />
                                </div>
                                <div>
                                    <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1 pl-1">Nom (Patronyme)</label>
                                    <input v-model="form.last_name" type="text" class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold transition-all" required />
                                </div>
                            </div>
                            <div class="grid grid-cols-3 gap-6">
                                <div>
                                    <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1 pl-1">Sexe</label>
                                    <select v-model="form.gender" class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold">
                                        <option value="M">Masculin</option>
                                        <option value="F">Féminin</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1 pl-1">Date de Naissance</label>
                                    <input v-model="form.date_of_birth" type="date" class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold" required />
                                </div>
                                <div>
                                    <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1 pl-1">Heure de Naissance</label>
                                    <input v-model="form.time_of_birth" type="time" class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold" />
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1 pl-1">Lieu de Naissance</label>
                                    <input v-model="form.place_of_birth" type="text" class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold" required />
                                </div>
                                <div>
                                    <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1 pl-1">Formation Sanitaire (lieu d'accouchement)</label>
                                    <input v-model="form.health_facility" type="text" class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold" placeholder="Ex : Centre de Santé d'Enampore" />
                                </div>
                            </div>
                            <div class="flex items-center gap-2 py-2">
                                <input id="is_judgment" v-model="form.is_judgment" type="checkbox" class="h-4 w-4 rounded border-gray-300 text-[#1E690F] focus:ring-[#1E690F] focus:border-[#1E690F]" />
                                <label for="is_judgment" class="text-xs font-black text-gray-700 uppercase tracking-wider cursor-pointer">Cet acte fait suite à un Jugement de Naissance</label>
                            </div>
                            <div v-if="form.is_judgment" class="p-6 bg-green-50/30 rounded-2xl border border-green-100/50 space-y-4 col-span-full">
                                <h4 class="text-[10px] font-black text-[#1E690F] uppercase tracking-widest flex items-center gap-2">
                                    <div class="w-1.5 h-1.5 bg-[#1E690F] rounded-full"></div>
                                    Références du Jugement
                                </h4>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                    <div>
                                        <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1 pl-1">Numéro du Jugement</label>
                                        <input v-model="form.judgment_number" type="text" class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold" :required="form.is_judgment" />
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1 pl-1">Date du Jugement</label>
                                        <input v-model="form.judgment_date" type="date" class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold" :required="form.is_judgment" />
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1 pl-1">Tribunal</label>
                                        <input v-model="form.judgment_court" type="text" class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold" :required="form.is_judgment" placeholder="Tribunal d'Instance" />
                                    </div>
                                </div>
                            </div>
                        </div>


                        <div v-if="type === 'mariage'" class="space-y-6 col-span-full">
                            <div class="grid grid-cols-2 gap-6">
                                <div class="p-6 bg-blue-50/50 rounded-2xl border border-blue-100 shadow-sm transition-all hover:shadow-md">
                                    <h4 class="text-[10px] font-black text-blue-900 uppercase tracking-widest mb-4 flex items-center gap-2">
                                        <div class="w-1.5 h-1.5 bg-blue-600 rounded-full"></div>
                                        Épou (Mari)
                                    </h4>
                                    <div class="space-y-4">
                                        <input v-model="form.husband_first_name" placeholder="Prénoms" class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold" required />
                                        <input v-model="form.husband_last_name" placeholder="Nom" class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold" required />
                                    </div>
                                </div>
                                <div class="p-6 bg-pink-50/50 rounded-2xl border border-pink-100 shadow-sm transition-all hover:shadow-md">
                                    <h4 class="text-[10px] font-black text-pink-900 uppercase tracking-widest mb-4 flex items-center gap-2">
                                         <div class="w-1.5 h-1.5 bg-pink-600 rounded-full"></div>
                                        Épouse (Femme)
                                    </h4>
                                    <div class="space-y-4">
                                        <input v-model="form.wife_first_name" placeholder="Prénoms" class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold" required />
                                        <input v-model="form.wife_last_name" placeholder="Nom" class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold" required />
                                    </div>
                                </div>
                            </div>
                             <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1 pl-1">Date du Mariage</label>
                                    <input v-model="form.marriage_date" type="date" class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold" required />
                                </div>
                                <div>
                                    <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1 pl-1">Lieu de Célébration</label>
                                    <input v-model="form.marriage_place" type="text" class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold" required />
                                </div>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1 pl-1">Option Matrimoniale (Pluralité des liens)</label>
                                    <select v-model="form.marriage_option" class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold">
                                        <option value="monogamie">Monogamie</option>
                                        <option value="limitation_polygamie">Limitation de polygamie</option>
                                        <option value="polygamie">Polygamie</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1 pl-1">Régime Matrimonial</label>
                                    <select v-model="form.matrimonial_regime" class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold">
                                        <option value="separation_biens">Séparation des biens</option>
                                        <option value="regime_dotal">Régime dotal</option>
                                        <option value="participation_meubles_acquets">Régime communautaire de participation aux meubles et acquêts</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div v-if="type === 'deces'" class="space-y-6 col-span-full">
                            <div class="grid grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1 pl-1">Prénoms du défunt</label>
                                    <input v-model="form.deceased_first_name" type="text" class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold" required />
                                </div>
                                <div>
                                    <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1 pl-1">Nom du défunt</label>
                                    <input v-model="form.deceased_last_name" type="text" class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold" required />
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1 pl-1">Date du Décès</label>
                                    <input v-model="form.date_of_death" type="date" class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold" required />
                                </div>
                                <div>
                                    <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1 pl-1">Lieu du Décès</label>
                                    <input v-model="form.place_of_death" type="text" class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold" required />
                                </div>
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1 pl-1">Cause du Décès (Optionnel)</label>
                                <textarea v-model="form.cause_of_death" class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold" rows="2"></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 2: Filiation & Détails Parents -->
                <div v-if="type === 'naissance'" class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8">
                    <h3 class="text-xs font-black uppercase tracking-widest mb-6 flex items-center gap-2" style="color: #1E690F;">
                        <UserGroupIcon class="h-4 w-4" />
                        Filiation &amp; Détails Parents
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <!-- Père -->
                        <div class="p-6 bg-blue-50/40 rounded-2xl border border-blue-100 space-y-4">
                            <h4 class="text-[10px] font-black text-blue-900 uppercase tracking-widest flex items-center gap-2">
                                <div class="w-1.5 h-1.5 bg-blue-600 rounded-full"></div>
                                Père
                            </h4>
                            <div>
                                <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1 pl-1">Prénoms &amp; Nom du Père</label>
                                <input v-model="form.father_name" type="text" class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold" />
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1 pl-1">Profession</label>
                                <input v-model="form.parents_metadata.father_profession" type="text" class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold" />
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1 pl-1">Date de naissance</label>
                                    <input v-model="form.parents_metadata.father_date_of_birth" type="date" class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold" />
                                </div>
                                <div>
                                    <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1 pl-1">Lieu de naissance</label>
                                    <input v-model="form.parents_metadata.father_place_of_birth" type="text" class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold" />
                                </div>
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1 pl-1">Domicile</label>
                                <input v-model="form.parents_metadata.father_domicile" type="text" class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold" />
                            </div>
                        </div>
                        <!-- Mère -->
                        <div class="p-6 bg-pink-50/40 rounded-2xl border border-pink-100 space-y-4">
                            <h4 class="text-[10px] font-black text-pink-900 uppercase tracking-widest flex items-center gap-2">
                                <div class="w-1.5 h-1.5 bg-pink-500 rounded-full"></div>
                                Mère
                            </h4>
                            <div>
                                <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1 pl-1">Prénoms &amp; Nom de la Mère</label>
                                <input v-model="form.mother_name" type="text" class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold" />
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1 pl-1">Profession</label>
                                <input v-model="form.parents_metadata.mother_profession" type="text" class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold" />
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1 pl-1">Date de naissance</label>
                                    <input v-model="form.parents_metadata.mother_date_of_birth" type="date" class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold" />
                                </div>
                                <div>
                                    <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1 pl-1">Lieu de naissance</label>
                                    <input v-model="form.parents_metadata.mother_place_of_birth" type="text" class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold" />
                                </div>
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1 pl-1">Domicile</label>
                                <input v-model="form.parents_metadata.mother_domicile" type="text" class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 3: Déclarant -->
                <div v-if="type === 'naissance'" class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-xs font-black uppercase tracking-widest flex items-center gap-2" style="color: #1E690F;">
                            <UserIcon class="h-4 w-4" />
                            Déclarant
                        </h3>
                        <label class="flex items-center gap-2 cursor-pointer select-none">
                            <input id="hasDeclarant" v-model="hasDeclarant" type="checkbox" class="h-4 w-4 rounded border-gray-300 text-[#1E690F] focus:ring-[#1E690F]" />
                            <span class="text-[10px] font-black text-gray-500 uppercase tracking-widest">Déclarant différent des parents</span>
                        </label>
                    </div>
                    <div v-if="hasDeclarant" class="space-y-6">
                        <div class="grid grid-cols-2 gap-6">
                            <div>
                                <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1 pl-1">Prénoms du Déclarant</label>
                                <input v-model="form.parents_metadata.declarant_first_name" type="text" class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold" />
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1 pl-1">Nom du Déclarant</label>
                                <input v-model="form.parents_metadata.declarant_last_name" type="text" class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold" />
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-6">
                            <div>
                                <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1 pl-1">Profession</label>
                                <input v-model="form.parents_metadata.declarant_profession" type="text" class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold" />
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1 pl-1">Numéro d'Identification</label>
                                <input v-model="form.parents_metadata.declarant_id_number" type="text" class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold" />
                            </div>
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1 pl-1">Adresse du Déclarant</label>
                            <input v-model="form.parents_metadata.declarant_address" type="text" class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold" />
                        </div>
                        <div class="grid grid-cols-2 gap-6">
                            <div>
                                <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1 pl-1">Date de Déclaration</label>
                                <input v-model="form.parents_metadata.declarant_date" type="date" class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold" />
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1 pl-1">N° et Date du Jugement d'Autorisation</label>
                                <input v-model="form.parents_metadata.declarant_judgment_ref" type="text" class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold" placeholder="Ex : N°12 du 01/01/2026" />
                            </div>
                        </div>
                    </div>
                    <p v-else class="text-xs text-gray-400 italic">Cocher la case ci-dessus si le déclarant est différent des parents.</p>
                </div>


                <div v-if="type === 'mariage'" class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8">
                    <h3 class="text-xs font-black uppercase tracking-widest mb-6 flex items-center gap-2" style="color: #1E690F;">
                        <UserGroupIcon class="h-4 w-4" />
                        Témoins de l'acte
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-4 p-4 bg-gray-50/50 rounded-2xl border border-gray-100/50">
                            <h4 class="text-[10px] font-black text-gray-400 uppercase tracking-widest flex items-center gap-1.5">
                                <div class="w-1.5 h-1.5 bg-[#1E690F] rounded-full"></div>
                                Témoin 1
                            </h4>
                            <input v-model="form.witnesses_metadata.witness1_name" placeholder="Prénoms & Nom" class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold" />
                            <input v-model="form.witnesses_metadata.witness1_cni" placeholder="N° Carte d'Identité Nationale (CNI)" class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold" />
                        </div>
                        <div class="space-y-4 p-4 bg-gray-50/50 rounded-2xl border border-gray-100/50">
                            <h4 class="text-[10px] font-black text-gray-400 uppercase tracking-widest flex items-center gap-1.5">
                                <div class="w-1.5 h-1.5 bg-[#1E690F] rounded-full"></div>
                                Témoin 2
                            </h4>
                            <input v-model="form.witnesses_metadata.witness2_name" placeholder="Prénoms & Nom" class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold" />
                            <input v-model="form.witnesses_metadata.witness2_cni" placeholder="N° Carte d'Identité Nationale (CNI)" class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold" />
                        </div>
                        <div class="space-y-4 p-4 bg-gray-50/50 rounded-2xl border border-gray-100/50">
                            <h4 class="text-[10px] font-black text-gray-400 uppercase tracking-widest flex items-center gap-1.5">
                                <div class="w-1.5 h-1.5 bg-[#1E690F] rounded-full"></div>
                                Témoin 3
                            </h4>
                            <input v-model="form.witnesses_metadata.witness3_name" placeholder="Prénoms & Nom" class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold" />
                            <input v-model="form.witnesses_metadata.witness3_cni" placeholder="N° Carte d'Identité Nationale (CNI)" class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold" />
                        </div>
                        <div class="space-y-4 p-4 bg-gray-50/50 rounded-2xl border border-gray-100/50">
                            <h4 class="text-[10px] font-black text-gray-400 uppercase tracking-widest flex items-center gap-1.5">
                                <div class="w-1.5 h-1.5 bg-[#1E690F] rounded-full"></div>
                                Témoin 4
                            </h4>
                            <input v-model="form.witnesses_metadata.witness4_name" placeholder="Prénoms & Nom" class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold" />
                            <input v-model="form.witnesses_metadata.witness4_cni" placeholder="N° Carte d'Identité Nationale (CNI)" class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold" />
                        </div>
                    </div>
                </div>

                <!-- Pièces justificatives — Naissance (5 catégories PDF) -->
                <div v-if="type === 'naissance'" class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8">
                    <h3 class="text-xs font-black uppercase tracking-widest mb-6 flex items-center gap-2" style="color: #1E690F;">
                        <DocumentArrowUpIcon class="h-4 w-4" />
                        Pièces Justificatives (PDF par catégorie)
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- CNI Père -->
                        <div>
                            <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-2 pl-1">CNI du Père</label>
                            <label class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed rounded-2xl cursor-pointer hover:bg-gray-50/50 transition-all" :class="form.doc_cni_pere ? 'border-[#1E690F] bg-green-50/20' : 'border-gray-300 bg-white'">
                                <DocumentIcon class="w-6 h-6 mb-1" :class="form.doc_cni_pere ? 'text-[#1E690F]' : 'text-gray-400'" />
                                <p class="text-xs text-gray-500 font-bold text-center px-2"><span v-if="form.doc_cni_pere">{{ form.doc_cni_pere.name }}</span><span v-else>Cliquer pour téléverser (PDF)</span></p>
                                <input type="file" class="hidden" accept="application/pdf" @change="(e) => form.doc_cni_pere = e.target.files[0]" />
                            </label>
                        </div>
                        <!-- CNI Mère -->
                        <div>
                            <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-2 pl-1">CNI de la Mère</label>
                            <label class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed rounded-2xl cursor-pointer hover:bg-gray-50/50 transition-all" :class="form.doc_cni_mere ? 'border-[#1E690F] bg-green-50/20' : 'border-gray-300 bg-white'">
                                <DocumentIcon class="w-6 h-6 mb-1" :class="form.doc_cni_mere ? 'text-[#1E690F]' : 'text-gray-400'" />
                                <p class="text-xs text-gray-500 font-bold text-center px-2"><span v-if="form.doc_cni_mere">{{ form.doc_cni_mere.name }}</span><span v-else>Cliquer pour téléverser (PDF)</span></p>
                                <input type="file" class="hidden" accept="application/pdf" @change="(e) => form.doc_cni_mere = e.target.files[0]" />
                            </label>
                        </div>
                        <!-- Acte / Attestation de naissance -->
                        <div>
                            <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-2 pl-1">Acte / Attestation de Naissance</label>
                            <label class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed rounded-2xl cursor-pointer hover:bg-gray-50/50 transition-all" :class="form.doc_acte_naissance ? 'border-[#1E690F] bg-green-50/20' : 'border-gray-300 bg-white'">
                                <DocumentIcon class="w-6 h-6 mb-1" :class="form.doc_acte_naissance ? 'text-[#1E690F]' : 'text-gray-400'" />
                                <p class="text-xs text-gray-500 font-bold text-center px-2"><span v-if="form.doc_acte_naissance">{{ form.doc_acte_naissance.name }}</span><span v-else>Cliquer pour téléverser (PDF)</span></p>
                                <input type="file" class="hidden" accept="application/pdf" @change="(e) => form.doc_acte_naissance = e.target.files[0]" />
                            </label>
                        </div>
                        <!-- CNI Déclarant -->
                        <div>
                            <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-2 pl-1">CNI du Déclarant</label>
                            <label class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed rounded-2xl cursor-pointer hover:bg-gray-50/50 transition-all" :class="form.doc_cni_declarant ? 'border-[#1E690F] bg-green-50/20' : 'border-gray-300 bg-white'">
                                <DocumentIcon class="w-6 h-6 mb-1" :class="form.doc_cni_declarant ? 'text-[#1E690F]' : 'text-gray-400'" />
                                <p class="text-xs text-gray-500 font-bold text-center px-2"><span v-if="form.doc_cni_declarant">{{ form.doc_cni_declarant.name }}</span><span v-else>Cliquer pour téléverser (PDF)</span></p>
                                <input type="file" class="hidden" accept="application/pdf" @change="(e) => form.doc_cni_declarant = e.target.files[0]" />
                            </label>
                        </div>
                        <!-- Autres pièces -->
                        <div class="md:col-span-2">
                            <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-2 pl-1">Autres Pièces Justificatives (si nécessaire)</label>
                            <label class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed rounded-2xl cursor-pointer hover:bg-gray-50/50 transition-all" :class="form.doc_autres ? 'border-[#1E690F] bg-green-50/20' : 'border-gray-300 bg-white'">
                                <DocumentIcon class="w-6 h-6 mb-1" :class="form.doc_autres ? 'text-[#1E690F]' : 'text-gray-400'" />
                                <p class="text-xs text-gray-500 font-bold text-center px-2"><span v-if="form.doc_autres">{{ form.doc_autres.name }}</span><span v-else>Cliquer pour téléverser (PDF)</span></p>
                                <input type="file" class="hidden" accept="application/pdf" @change="(e) => form.doc_autres = e.target.files[0]" />
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Document Justificatif PDF (Mariage uniquement) -->
                <div v-if="type === 'mariage'" class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8">
                    <h3 class="text-xs font-black uppercase tracking-widest mb-6 flex items-center gap-2" style="color: #1E690F;">
                        <DocumentArrowUpIcon class="h-4 w-4" />
                        Document Justificatif Obligatoire (PDF)
                    </h3>
                    <div class="space-y-4">
                        <div class="flex items-center justify-center w-full">
                            <label class="flex flex-col items-center justify-center w-full h-48 border-2 border-dashed rounded-3xl cursor-pointer hover:bg-gray-50/50 transition-all"
                                   :class="form.certificate_file ? 'border-[#1E690F] bg-green-50/20' : 'border-gray-300 bg-white'">
                                <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                    <DocumentIcon class="w-10 h-10 mb-3" :class="form.certificate_file || form.certificate_path ? 'text-[#1E690F]' : 'text-gray-400'" />
                                    <p class="mb-2 text-sm text-gray-500 font-bold">
                                        <span v-if="form.certificate_file">Fichier sélectionné : {{ form.certificate_file.name }}</span>
                                        <span v-else-if="form.certificate_path">Modifier le document justificatif PDF</span>
                                        <span v-else>Glisser-déposer le certificat ou cliquer pour téléverser</span>
                                    </p>
                                    <p class="text-xs text-gray-400">Format autorisé : PDF uniquement (Max 10 Mo)</p>
                                </div>
                                <input type="file" class="hidden" accept="application/pdf" @change="(e) => form.certificate_file = e.target.files[0]" />
                            </label>
                        </div>
                        <div v-if="form.certificate_path && !form.certificate_file" class="flex items-center gap-2 p-3 bg-gray-50 rounded-xl border border-gray-100">
                            <CheckCircleIcon class="w-5 h-5 text-[#1E690F]" />
                            <span class="text-xs font-bold text-gray-600">Un document justificatif existe déjà.</span>
                            <a :href="form.certificate_path" target="_blank" class="text-xs font-black text-[#1E690F] hover:underline ml-auto">Visualiser</a>
                        </div>
                    </div>
                </div>

                <!-- Date d'inscription de l'acte (Naissance) -->
                <div v-if="type === 'naissance'" class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8">
                    <h3 class="text-xs font-black uppercase tracking-widest mb-6 flex items-center gap-2" style="color: #1E690F;">
                        <CalendarIcon class="h-4 w-4" />
                        Date d'Inscription de l'Acte
                    </h3>
                    <div>
                        <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1 pl-1">Fait à Enampore, le</label>
                        <input v-model="form.act_registration_date" type="date" class="w-full md:w-72 px-4 py-3 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold" />
                    </div>
                </div>

                <!-- Mentions marginales / Observations -->
                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8">
                    <h3 class="text-xs font-black uppercase tracking-widest mb-6 flex items-center gap-2" style="color: #1E690F;">
                        <PlusCircleIcon class="h-4 w-4" />
                        {{ type === 'naissance' ? 'Mentions Marginales' : "Observations de l'Officier" }}
                    </h3>
                    <div>
                        <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1 pl-1">
                            {{ type === 'naissance' ? 'Mentions marginales' : 'Commentaires officiels' }}
                        </label>
                        <textarea v-model="form.officer_comments" class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold" rows="3" placeholder="Notes additionnelles, mentions marginales..."></textarea>
                    </div>
                </div>

                <div class="flex justify-end pt-6 gap-4">
                     <Link :href="`/acts/${type}`" class="inline-flex items-center justify-center px-10 py-4 bg-white border border-gray-200 rounded-2xl font-black text-xs text-gray-500 uppercase tracking-widest hover:bg-gray-50 transition-all">
                        Annuler
                    </Link>
                    <button type="submit" 
                            :disabled="form.processing"
                            class="inline-flex items-center justify-center px-10 py-4 border border-transparent rounded-2xl font-black text-xs text-white uppercase tracking-widest hover:bg-[#185709] shadow-2xl shadow-green-100 transition-all active:scale-95 disabled:bg-gray-400"
                            style="background-color: #1E690F;">
                        <PlusCircleIcon v-if="!form.processing" class="w-5 h-5 mr-2 stroke-[3]" />
                        {{ form.processing ? 'Chargement...' : (is_edit ? 'Mettre à jour l\'Acte' : 'Enregistrer l\'Acte Officiel') }}
                    </button>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
