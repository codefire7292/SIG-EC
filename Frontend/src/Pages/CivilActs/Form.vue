<script setup>
import { computed, ref, watch } from 'vue';
import { useForm, Head, Link } from '@inertiajs/vue3';
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
    PlusIcon,
    TrashIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
    act: Object,
    type: String,
    is_edit: Boolean,
    registries: {
        type: Array,
        default: () => []
    },
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

const formatDate = (dateStr) => {
    if (!dateStr) return '';
    return dateStr.substring(0, 10);
};

const formatTime = (timeStr) => {
    if (!timeStr) return '';
    return timeStr.substring(0, 5);
};

const formatDateTimeLocal = (dateTimeStr) => {
    if (!dateTimeStr) return '';
    let clean = dateTimeStr.replace(' ', 'T');
    if (clean.length >= 16) {
        return clean.substring(0, 16);
    }
    return clean;
};

const urlParams = new URLSearchParams(typeof window !== 'undefined' ? window.location.search : '');
const isOldRegistryMode = ref(urlParams.get('old_registry') === '1');


const form = useForm({
    // Common
    is_old_registry: isOldRegistryMode.value,
    registry_id: props.act?.registry_id || '',
    reference_number: '',
    officer_comments: props.act?.officer_comments || '',
    certificate_file: null,
    certificate_path: props.act?.certificate_path || null,
    
    // Naissance — Identité de l'enfant
    first_name: props.act?.first_name || '',
    last_name: props.act?.last_name || '',
    date_of_birth: formatDate(props.act?.date_of_birth),
    time_of_birth: formatTime(props.act?.time_of_birth),
    place_of_birth: props.act?.place_of_birth || '',
    health_facility: props.act?.health_facility || '',
    act_registration_date: formatDate(props.act?.act_registration_date),
    gender: props.act?.gender || 'M',
    is_judgment: props.act?.is_judgment || false,
    judgment_number: props.act?.judgment_number || '',
    judgment_date: formatDate(props.act?.judgment_date),
    judgment_court: props.act?.judgment_court || '',
    father_name: props.act?.father_name || '',
    mother_name: props.act?.mother_name || '',
    parents_metadata: {
        father_profession: props.act?.parents_metadata?.father_profession || '',
        father_date_of_birth: formatDate(props.act?.parents_metadata?.father_date_of_birth),
        father_place_of_birth: props.act?.parents_metadata?.father_place_of_birth || '',
        father_domicile: props.act?.parents_metadata?.father_domicile || '',
        mother_profession: props.act?.parents_metadata?.mother_profession || '',
        mother_date_of_birth: formatDate(props.act?.parents_metadata?.mother_date_of_birth),
        mother_place_of_birth: props.act?.parents_metadata?.mother_place_of_birth || '',
        mother_domicile: props.act?.parents_metadata?.mother_domicile || '',
        declarant_first_name: props.act?.parents_metadata?.declarant_first_name || '',
        declarant_last_name: props.act?.parents_metadata?.declarant_last_name || '',
        declarant_profession: props.act?.parents_metadata?.declarant_profession || '',
        declarant_address: props.act?.parents_metadata?.declarant_address || '',
        declarant_id_number: props.act?.parents_metadata?.declarant_id_number || '',
        declarant_date: formatDate(props.act?.parents_metadata?.declarant_date),
        declarant_judgment_ref: props.act?.parents_metadata?.declarant_judgment_ref || '',
        judgment_auth_date: formatDate(props.act?.parents_metadata?.judgment_auth_date),
        judgment_auth_ref: props.act?.parents_metadata?.judgment_auth_ref || '',
        witnesses: (props.act?.parents_metadata?.witnesses || []).map(w => ({
            ...w,
            date_of_birth: formatDate(w.date_of_birth)
        })),
    },
    // Naissance — Pièces justificatives PDF
    doc_cni_pere: null,
    doc_cni_mere: null,
    doc_acte_naissance: null,
    doc_cni_declarant: null,
    doc_autres: null,
    doc_jugement: null,

    // Mariage
    husband_first_name: props.act?.husband_first_name || '',
    husband_last_name: props.act?.husband_last_name || '',
    wife_first_name: props.act?.wife_first_name || '',
    wife_last_name: props.act?.wife_last_name || '',
    marriage_date: formatDate(props.act?.marriage_date),
    marriage_place: props.act?.marriage_place || '',
    marriage_option: props.act?.marriage_option || 'monogamie',
    matrimonial_regime: props.act?.matrimonial_regime || 'separation_biens',
    is_judgment: props.act?.is_judgment || false,
    judgment_number: props.act?.judgment_number || '',
    judgment_date: formatDate(props.act?.judgment_date),
    spouses_metadata: props.act?.spouses_metadata ? {
        ...props.act.spouses_metadata,
        husband_date_of_birth: formatDate(props.act.spouses_metadata.husband_date_of_birth),
        wife_date_of_birth: formatDate(props.act.spouses_metadata.wife_date_of_birth),
        husband_father_date_of_birth: formatDate(props.act.spouses_metadata.husband_father_date_of_birth),
        husband_mother_date_of_birth: formatDate(props.act.spouses_metadata.husband_mother_date_of_birth),
        wife_father_date_of_birth: formatDate(props.act.spouses_metadata.wife_father_date_of_birth),
        wife_mother_date_of_birth: formatDate(props.act.spouses_metadata.wife_mother_date_of_birth),
    } : {
        husband_date_of_birth: '',
        husband_place_of_birth: '',
        husband_profession: '',
        husband_domicile: '',
        husband_residence: '',
        husband_married_to: '',
        wife_date_of_birth: '',
        wife_place_of_birth: '',
        wife_profession: '',
        wife_domicile: '',
        wife_residence: '',
        husband_father_first_name: '',
        husband_father_last_name: '',
        husband_father_date_of_birth: '',
        husband_father_profession: '',
        husband_father_domicile: '',
        husband_mother_first_name: '',
        husband_mother_last_name: '',
        husband_mother_date_of_birth: '',
        husband_mother_profession: '',
        husband_mother_domicile: '',
        wife_father_first_name: '',
        wife_father_last_name: '',
        wife_father_date_of_birth: '',
        wife_father_profession: '',
        wife_father_domicile: '',
        wife_mother_first_name: '',
        wife_mother_last_name: '',
        wife_mother_date_of_birth: '',
        wife_mother_profession: '',
        wife_mother_domicile: '',
        max_wives: ''
    },
    witnesses_metadata: Array.isArray(props.act?.witnesses_metadata)
        ? props.act.witnesses_metadata
        : [],
    // Mariage — Pièces justificatives PDF
    doc_cni_husband: null,
    doc_cni_wife: null,
    doc_birth_husband: null,
    doc_birth_wife: null,
    doc_domicile: null,
    doc_medical: null,
    doc_parental_auth: null,
    doc_jugement: null,

    // Deces
    deceased_first_name: props.act?.deceased_first_name || '',
    deceased_last_name: props.act?.deceased_last_name || '',
    date_of_death: formatDate(props.act?.date_of_death),
    place_of_death: props.act?.place_of_death || '',
    cause_of_death: props.act?.cause_of_death || '',
    time_of_death: props.act?.time_of_death || '',
    health_facility: props.act?.health_facility || '',
    act_registration_date: formatDate(props.act?.act_registration_date),
    gender: props.act?.gender || 'M',
    date_of_birth: formatDate(props.act?.date_of_birth),
    
    // Pièces justificatives PDF Décès
    doc_death_cert: null,
    doc_deceased_id: null,
    doc_declarant_id: null,
    doc_jugement: null,
    doc_autres: null,

    // Death Metadata JSON
    death_metadata: props.act?.death_metadata ? {
        ...props.act.death_metadata,
        father_date_of_birth: formatDate(props.act.death_metadata.father_date_of_birth),
        mother_date_of_birth: formatDate(props.act.death_metadata.mother_date_of_birth),
        declarant_date_time: formatDateTimeLocal(props.act.death_metadata.declarant_date_time),
    } : {
        time_of_birth: '',
        place_of_birth: '',
        profession: '',
        domicile: '',
        marital_status: 'Célibataire',
        previously_married_to: '',
        
        // Parents
        father_first_name: '',
        father_last_name: '',
        father_date_of_birth: '',
        father_profession: '',
        father_domicile: '',
        mother_first_name: '',
        mother_last_name: '',
        mother_date_of_birth: '',
        mother_profession: '',
        mother_domicile: '',

        // Declarant
        declarant_first_name: '',
        declarant_last_name: '',
        declarant_profession: '',
        declarant_address: '',
        declarant_relationship: '',
        declarant_id_number: '',
        declarant_date_time: '',
    },
});

// --- Ancien registre : numéro d'acte auto-calculé ---
const actNumber = ref('');

const selectedRegistry = computed(() => {
    if (!form.registry_id) return null;
    return props.registries.find(r => r.id === form.registry_id) || null;
});

const fullReferenceNumber = computed(() => {
    if (!selectedRegistry.value || !actNumber.value) return '';
    const prefix = selectedRegistry.value.reference_prefix;
    const padded = String(actNumber.value).padStart(4, '0');
    return `${prefix}-${padded}`;
});

// Plage de numéros selon le volume (Volume 1 = 1-50, Volume 2 = 51-100, ...)
const volumeRange = computed(() => {
    if (!selectedRegistry.value) return [];
    const vol = selectedRegistry.value.number || 1;
    const start = (vol - 1) * 50 + 1;
    const end = vol * 50;
    const range = [];
    for (let i = start; i <= end; i++) range.push(i);
    return range;
});

watch(fullReferenceNumber, (val) => {
    form.reference_number = val;
});

watch(() => form.registry_id, () => {
    actNumber.value = '';
    form.reference_number = '';
});

const addMarriageWitness = () => {
    if (!Array.isArray(form.witnesses_metadata)) {
        form.witnesses_metadata = [];
    }
    form.witnesses_metadata.push({
        first_name: '',
        last_name: '',
        profession: '',
        address: '',
        id_number: '',
        cni_file: null,
        doc_cni_path: null
    });
};

const removeMarriageWitness = (index) => {
    form.witnesses_metadata.splice(index, 1);
};

const addWitness = () => {
    if (!form.parents_metadata.witnesses) {
        form.parents_metadata.witnesses = [];
    }
    if (form.parents_metadata.witnesses.length < 2) {
        form.parents_metadata.witnesses.push({
            first_name: '',
            last_name: '',
            date_of_birth: '',
            place_of_birth: '',
            profession: '',
            address: '',
            id_number: '',
        });
    }
};

const removeWitness = (index) => {
    form.parents_metadata.witnesses.splice(index, 1);
};

const showErrorModal = ref(false);
const modalErrorMessage = ref('');
const modalErrorTitle = ref('');

const closeErrorModal = () => {
    showErrorModal.value = false;
    modalErrorMessage.value = '';
    modalErrorTitle.value = '';
};

const handleFileChange = (event, fieldOrObject, objectField = null) => {
    const file = event.target.files[0];
    if (!file) return;

    const maxSize = 500 * 1024; // 500KB
    const fieldName = objectField ? objectField : fieldOrObject;

    // Check if the file is a PDF
    const isPdf = file.type === 'application/pdf' || file.name.toLowerCase().endsWith('.pdf');
    if (!isPdf) {
        const errorMsg = `Le fichier "${file.name}" n'est pas un document PDF valide. Seuls les fichiers au format PDF sont autorisés.`;
        form.errors[fieldName] = errorMsg;
        event.target.value = '';
        modalErrorTitle.value = "Format de fichier invalide";
        modalErrorMessage.value = errorMsg;
        showErrorModal.value = true;
        return;
    }

    if (file.size > maxSize) {
        const errorMsg = `Le fichier "${file.name}" dépasse la limite autorisée de 500 Ko (taille actuelle : ${(file.size / 1024).toFixed(1)} Ko).`;
        form.errors[fieldName] = errorMsg;
        event.target.value = '';
        modalErrorTitle.value = "Fichier trop volumineux";
        modalErrorMessage.value = errorMsg;
        showErrorModal.value = true;
        return;
    }

    if (form.errors[fieldName]) {
        delete form.errors[fieldName];
    }

    if (objectField) {
        fieldOrObject[objectField] = file;
    } else {
        form[fieldOrObject] = file;
    }
};

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
            <!-- Banner d'erreurs de validation -->
            <div v-if="Object.keys(form.errors).length > 0" class="p-6 mb-8 bg-red-50 border border-red-200 rounded-3xl text-red-800 shadow-sm">
                <div class="flex items-center gap-2 mb-3">
                    <svg class="h-5 w-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <span class="font-black text-sm uppercase tracking-wider">Veuillez corriger les erreurs suivantes :</span>
                </div>
                <ul class="list-disc pl-5 text-xs font-bold space-y-1">
                    <li v-for="(error, field) in form.errors" :key="field">
                        {{ error }}
                    </li>
                </ul>
            </div>

            <form @submit.prevent="submit" class="space-y-8" novalidate>
                
                <!-- Section 1: Informations de Base -->
                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8">
                    <h3 class="text-xs font-black uppercase tracking-widest mb-6 flex items-center gap-2" style="color: #1E690F;">
                        <IdentificationIcon class="h-4 w-4" />
                        <span v-if="type === 'mariage'">Informations des époux</span>
                        <span v-else>Informations d'Identification</span>
                    </h3>
                    <div v-if="form.is_old_registry" class="mb-8 p-6 bg-amber-50 rounded-2xl border border-amber-200">
                        <h4 class="text-xs font-black text-amber-900 uppercase tracking-widest mb-4 flex items-center gap-2">
                            <DocumentIcon class="h-4 w-4" />
                            Saisie dans un Ancien Registre
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-[10px] font-black text-amber-800 uppercase tracking-widest mb-1 pl-1">Registre Cible <span class="text-red-500">*</span></label>
                                <select v-model="form.registry_id" class="w-full px-4 py-3 rounded-xl border border-amber-300 bg-white focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500 font-bold transition-all" required>
                                    <option value="">-- Sélectionner un registre --</option>
                                    <option v-for="reg in registries" :key="reg.id" :value="reg.id">
                                        Année {{ reg.year }} - Volume {{ reg.number }} (Préfixe: {{ reg.reference_prefix }})
                                    </option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-amber-800 uppercase tracking-widest mb-1 pl-1">Numéro de Référence de l'Acte <span class="text-red-500">*</span></label>
                                <select v-model="actNumber" :disabled="!form.registry_id" class="w-full px-4 py-3 rounded-xl border focus:outline-none focus:ring-2 font-bold transition-all disabled:opacity-50 disabled:cursor-not-allowed bg-white" :class="form.errors.reference_number ? 'border-red-400 focus:ring-red-400' : 'border-amber-300 focus:ring-amber-500 focus:border-amber-500'" required>
                                    <option value="">-- N° dans le registre --</option>
                                    <option v-for="n in volumeRange" :key="n" :value="n">{{ n }}</option>
                                </select>
                                <p v-if="fullReferenceNumber && !form.errors.reference_number" class="mt-2 pl-1 text-[10px] font-black text-amber-700 uppercase tracking-widest">
                                    Référence générée : <span class="text-amber-900">{{ fullReferenceNumber }}</span>
                                </p>
                                <p v-if="form.errors.reference_number" class="mt-2 pl-1 text-[10px] font-black text-red-600 flex items-center gap-1">
                                    <svg class="h-3 w-3 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                    {{ form.errors.reference_number }}
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div v-if="type === 'naissance'" class="space-y-6 col-span-full">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1 pl-1">Prénoms de l'enfant <span class="text-red-500">*</span></label>
                                    <input v-model="form.first_name" type="text" class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold transition-all" required />
                                </div>
                                <div>
                                    <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1 pl-1">Nom (Patronyme) <span class="text-red-500">*</span></label>
                                    <input v-model="form.last_name" type="text" class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold transition-all" required />
                                </div>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div>
                                    <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1 pl-1">Sexe <span class="text-red-500">*</span></label>
                                    <select v-model="form.gender" class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold">
                                        <option value="M">Masculin</option>
                                        <option value="F">Féminin</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1 pl-1">Date de Naissance <span class="text-red-500">*</span></label>
                                    <input v-model="form.date_of_birth" type="date" class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold" required />
                                </div>
                                <div>
                                    <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1 pl-1">
                                        Heure de Naissance
                                        <span v-if="!form.is_old_registry" class="text-red-500"> *</span>
                                    </label>
                                    <input v-model="form.time_of_birth" type="time" class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold" :required="!form.is_old_registry" />
                                </div>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1 pl-1">Lieu de Naissance <span class="text-red-500">*</span></label>
                                    <input v-model="form.place_of_birth" type="text" class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold" required />
                                </div>
                                <div>
                                    <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1 pl-1">
                                        Formation Sanitaire (lieu d'accouchement)
                                        <span v-if="!form.is_old_registry" class="text-red-500"> *</span>
                                    </label>
                                    <input v-model="form.health_facility" type="text" class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold" placeholder="Ex : Centre de Santé d'Enampore" :required="!form.is_old_registry" />
                                </div>
                            </div>
                        </div>
                           <div v-if="type === 'mariage'" class="space-y-6 col-span-full">
                            <!-- Les Époux -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Époux -->
                                <div class="p-6 bg-blue-50/50 rounded-2xl border border-blue-100 shadow-sm space-y-4">
                                    <h4 class="text-[10px] font-black text-blue-900 uppercase tracking-widest flex items-center gap-2">
                                        <div class="w-1.5 h-1.5 bg-blue-600 rounded-full"></div>
                                        Épou (Mari)
                                    </h4>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-[9px] font-black text-gray-500 uppercase tracking-widest mb-1">Prénoms <span class="text-red-500">*</span></label>
                                            <input v-model="form.husband_first_name" type="text" class="w-full px-4 py-2.5 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold text-sm" required />
                                        </div>
                                        <div>
                                            <label class="block text-[9px] font-black text-gray-500 uppercase tracking-widest mb-1">Nom <span class="text-red-500">*</span></label>
                                            <input v-model="form.husband_last_name" type="text" class="w-full px-4 py-2.5 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold text-sm" required />
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-[9px] font-black text-gray-500 uppercase tracking-widest mb-1">Date de naissance <span class="text-red-500">*</span></label>
                                            <input v-model="form.spouses_metadata.husband_date_of_birth" type="date" class="w-full px-4 py-2.5 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold text-sm" required />
                                        </div>
                                        <div>
                                            <label class="block text-[9px] font-black text-gray-500 uppercase tracking-widest mb-1">Lieu de naissance <span class="text-red-500">*</span></label>
                                            <input v-model="form.spouses_metadata.husband_place_of_birth" type="text" class="w-full px-4 py-2.5 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold text-sm" required />
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-[9px] font-black text-gray-500 uppercase tracking-widest mb-1">Profession <span class="text-red-500">*</span></label>
                                        <input v-model="form.spouses_metadata.husband_profession" type="text" class="w-full px-4 py-2.5 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold text-sm" required />
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-[9px] font-black text-gray-500 uppercase tracking-widest mb-1">Domicile <span class="text-red-500">*</span></label>
                                            <input v-model="form.spouses_metadata.husband_domicile" type="text" class="w-full px-4 py-2.5 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold text-sm" required />
                                        </div>
                                        <div>
                                            <label class="block text-[9px] font-black text-gray-500 uppercase tracking-widest mb-1">Résidence <span class="text-red-500">*</span></label>
                                            <input v-model="form.spouses_metadata.husband_residence" type="text" class="w-full px-4 py-2.5 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold text-sm" required />
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-[9px] font-black text-gray-500 uppercase tracking-widest mb-1">Marié à (identité/nombre des épouses existantes)</label>
                                        <input v-model="form.spouses_metadata.husband_married_to" type="text" placeholder="Ex: Marié à Aminata Diallo, 1 épouse" class="w-full px-4 py-2.5 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold text-sm" />
                                    </div>
                                </div>

                                <!-- Épouse -->
                                <div class="p-6 bg-pink-50/50 rounded-2xl border border-pink-100 shadow-sm space-y-4">
                                    <h4 class="text-[10px] font-black text-pink-900 uppercase tracking-widest flex items-center gap-2">
                                        <div class="w-1.5 h-1.5 bg-pink-600 rounded-full"></div>
                                        Épouse (Femme)
                                    </h4>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-[9px] font-black text-gray-500 uppercase tracking-widest mb-1">Prénoms <span class="text-red-500">*</span></label>
                                            <input v-model="form.wife_first_name" type="text" class="w-full px-4 py-2.5 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold text-sm" required />
                                        </div>
                                        <div>
                                            <label class="block text-[9px] font-black text-gray-500 uppercase tracking-widest mb-1">Nom <span class="text-red-500">*</span></label>
                                            <input v-model="form.wife_last_name" type="text" class="w-full px-4 py-2.5 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold text-sm" required />
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-[9px] font-black text-gray-500 uppercase tracking-widest mb-1">Date de naissance <span class="text-red-500">*</span></label>
                                            <input v-model="form.spouses_metadata.wife_date_of_birth" type="date" class="w-full px-4 py-2.5 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold text-sm" required />
                                        </div>
                                        <div>
                                            <label class="block text-[9px] font-black text-gray-500 uppercase tracking-widest mb-1">Lieu de naissance <span class="text-red-500">*</span></label>
                                            <input v-model="form.spouses_metadata.wife_place_of_birth" type="text" class="w-full px-4 py-2.5 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold text-sm" required />
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-[9px] font-black text-gray-500 uppercase tracking-widest mb-1">Profession <span class="text-red-500">*</span></label>
                                        <input v-model="form.spouses_metadata.wife_profession" type="text" class="w-full px-4 py-2.5 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold text-sm" required />
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-[9px] font-black text-gray-500 uppercase tracking-widest mb-1">Domicile <span class="text-red-500">*</span></label>
                                            <input v-model="form.spouses_metadata.wife_domicile" type="text" class="w-full px-4 py-2.5 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold text-sm" required />
                                        </div>
                                        <div>
                                            <label class="block text-[9px] font-black text-gray-500 uppercase tracking-widest mb-1">Résidence <span class="text-red-500">*</span></label>
                                            <input v-model="form.spouses_metadata.wife_residence" type="text" class="w-full px-4 py-2.5 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold text-sm" required />
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Célébration -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4 border-t border-gray-100">
                                <div>
                                    <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1 pl-1">Date du Mariage <span class="text-red-500">*</span></label>
                                    <input v-model="form.marriage_date" type="date" class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold" required />
                                </div>
                                <div>
                                    <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1 pl-1">Lieu de Célébration <span class="text-red-500">*</span></label>
                                    <input v-model="form.marriage_place" type="text" class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold" required />
                                </div>
                            </div>

                            <!-- Régime & Polygamie -->
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

                            <!-- Polygamie: Nombre max d'épouses -->
                            <div v-if="['limitation_polygamie', 'polygamie'].includes(form.marriage_option)" class="p-4 bg-yellow-50/50 rounded-2xl border border-yellow-100 max-w-md">
                                <label class="block text-[10px] font-black text-yellow-900 uppercase tracking-widest mb-2 pl-1">Nombre maximal d'épouses autorisées (Législation Sénégalaise)</label>
                                <select v-model="form.spouses_metadata.max_wives" class="w-full px-4 py-2.5 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold text-sm">
                                    <option value="">Sélectionner la limite...</option>
                                    <option value="1">1 épouse (Monogamie de fait)</option>
                                    <option value="2">2 épouses</option>
                                    <option value="3">3 épouses</option>
                                    <option value="4">4 épouses (Limite légale)</option>
                                </select>
                            </div>

                            <!-- Section Jugement (Mariage) -->
                            <div class="p-6 bg-gray-50 rounded-2xl border border-gray-200/80 space-y-4">
                                <div class="flex items-center gap-3">
                                    <input v-model="form.is_judgment" type="checkbox" id="marriage_is_judgment" class="h-4.5 w-4.5 text-[#1E690F] focus:ring-[#1E690F] border-gray-300 rounded" />
                                    <label for="marriage_is_judgment" class="text-xs font-black text-gray-700 uppercase tracking-wider cursor-pointer">Déclaration sur jugement d'autorisation ?</label>
                                </div>

                                <div v-if="form.is_judgment" class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-2">
                                    <div>
                                        <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1 pl-1">Numéro du Jugement</label>
                                        <input v-model="form.judgment_number" type="text" class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold" :required="form.is_judgment" />
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1 pl-1">Date du Jugement</label>
                                        <input v-model="form.judgment_date" type="date" class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold" :required="form.is_judgment" />
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1 pl-1">Copie du Jugement (PDF, max. 500 Ko)</label>
                                        <label class="flex flex-col items-center justify-center w-full h-12 border-2 border-dashed rounded-xl cursor-pointer hover:bg-gray-50/50 transition-all" :class="form.doc_jugement ? 'border-[#1E690F] bg-green-50' : 'border-gray-300 bg-white'">
                                            <div class="flex items-center gap-2">
                                                <DocumentIcon class="w-4 h-4" :class="form.doc_jugement ? 'text-[#1E690F]' : 'text-gray-400'" />
                                                <span class="text-xs text-gray-500 font-bold"><span v-if="form.doc_jugement">{{ form.doc_jugement.name }}</span><span v-else>Téléverser le PDF (max. 500 Ko)</span></span>
                                            </div>
                                            <input type="file" class="hidden" accept="application/pdf" @change="(e) => handleFileChange(e, 'doc_jugement')" />
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div v-if="type === 'deces'" class="space-y-6 col-span-full">
                            <!-- Informations de l'acte -->
                            <div class="p-6 bg-gray-50/50 rounded-2xl border border-gray-150 space-y-4">
                                <h4 class="text-[10px] font-black text-gray-500 uppercase tracking-widest flex items-center gap-2">
                                    <div class="w-1.5 h-1.5 bg-[#1E690F] rounded-full"></div>
                                    Informations de l'acte de décès
                                </h4>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                    <div>
                                        <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1 pl-1">Date du Décès <span class="text-red-500">*</span></label>
                                        <input v-model="form.date_of_death" type="date" class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold" required />
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1 pl-1">Heure du Décès <span class="text-red-500">*</span></label>
                                        <input v-model="form.time_of_death" type="time" class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold" required />
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1 pl-1">Date d'inscription de l'acte <span class="text-red-500">*</span></label>
                                        <input v-model="form.act_registration_date" type="date" class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold" required />
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1 pl-1">Lieu du Décès <span class="text-red-500">*</span></label>
                                        <input v-model="form.place_of_death" type="text" class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold" required placeholder="Ex : Enampore" />
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1 pl-1">Formation Sanitaire (Lieu de décès) <span class="text-red-500">*</span></label>
                                        <input v-model="form.health_facility" type="text" class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold" placeholder="Ex : Poste de santé d'Enampore" required />
                                    </div>
                                </div>
                            </div>

                            <!-- Informations sur le défunt -->
                            <div class="p-6 bg-gray-50/50 rounded-2xl border border-gray-150 space-y-4">
                                <h4 class="text-[10px] font-black text-gray-500 uppercase tracking-widest flex items-center gap-2">
                                    <div class="w-1.5 h-1.5 bg-[#1E690F] rounded-full"></div>
                                    Informations sur le défunt
                                </h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1 pl-1">Prénoms du défunt <span class="text-red-500">*</span></label>
                                        <input v-model="form.deceased_first_name" type="text" class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold" required />
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1 pl-1">Nom du défunt <span class="text-red-500">*</span></label>
                                        <input v-model="form.deceased_last_name" type="text" class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold" required />
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                    <div>
                                        <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1 pl-1">Sexe <span class="text-red-500">*</span></label>
                                        <select v-model="form.gender" class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold" required>
                                            <option value="M">Masculin</option>
                                            <option value="F">Féminin</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1 pl-1">Date de Naissance du défunt <span class="text-red-500">*</span></label>
                                        <input v-model="form.date_of_birth" type="date" class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold" required />
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1 pl-1">Heure de Naissance (si disponible)</label>
                                        <input v-model="form.death_metadata.time_of_birth" type="time" class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold" />
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                    <div>
                                        <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1 pl-1">Lieu de Naissance <span class="text-red-500">*</span></label>
                                        <input v-model="form.death_metadata.place_of_birth" type="text" class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold" required />
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1 pl-1">Profession du défunt <span class="text-red-500">*</span></label>
                                        <input v-model="form.death_metadata.profession" type="text" class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold" required />
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1 pl-1">Domicile du défunt <span class="text-red-500">*</span></label>
                                        <input v-model="form.death_metadata.domicile" type="text" class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold" required />
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1 pl-1">Situation Matrimoniale <span class="text-red-500">*</span></label>
                                        <select v-model="form.death_metadata.marital_status" class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold" required>
                                            <option value="Célibataire">Célibataire</option>
                                            <option value="Marié(e)">Marié(e)</option>
                                            <option value="Divorcé(e)">Divorcé(e)</option>
                                            <option value="Veuf/Veuve">Veuf/Veuve</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1 pl-1">Précédemment marié à</label>
                                        <input v-model="form.death_metadata.previously_married_to" type="text" class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold" placeholder="Nom du conjoint/précédent conjoint" />
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1 pl-1">Cause du Décès (Optionnel)</label>
                                    <textarea v-model="form.cause_of_death" class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold" rows="2"></textarea>
                                </div>
                            </div>

                            <!-- Section Jugement (Décès) -->
                            <div class="p-6 bg-gray-50 rounded-2xl border border-gray-200/80 space-y-4">
                                <div class="flex items-center gap-3">
                                    <input v-model="form.is_judgment" type="checkbox" id="death_is_judgment" class="h-4.5 w-4.5 text-[#1E690F] focus:ring-[#1E690F] border-gray-300 rounded" />
                                    <label for="death_is_judgment" class="text-xs font-black text-gray-700 uppercase tracking-wider cursor-pointer">Déclaration sur jugement d'autorisation ?</label>
                                </div>

                                <div v-if="form.is_judgment" class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-2">
                                    <div>
                                        <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1 pl-1">Numéro du Jugement</label>
                                        <input v-model="form.judgment_number" type="text" class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold" :required="form.is_judgment" />
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1 pl-1">Date du Jugement</label>
                                        <input v-model="form.judgment_date" type="date" class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold" :required="form.is_judgment" />
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1 pl-1">Juridiction</label>
                                        <input v-model="form.judgment_court" type="text" class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold" :required="form.is_judgment" placeholder="Ex : Tribunal de Kolda" />
                                    </div>
                                </div>
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
                                <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1 pl-1">Prénoms &amp; Nom du Père <span class="text-red-500">*</span></label>
                                <input v-model="form.father_name" type="text" class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold" required />
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1 pl-1">Profession <span class="text-red-500">*</span></label>
                                <input v-model="form.parents_metadata.father_profession" type="text" class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold" required />
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1 pl-1">Date de naissance <span class="text-red-500">*</span></label>
                                    <input v-model="form.parents_metadata.father_date_of_birth" type="date" class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold" required />
                                </div>
                                <div>
                                    <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1 pl-1">Lieu de naissance <span class="text-red-500">*</span></label>
                                    <input v-model="form.parents_metadata.father_place_of_birth" type="text" class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold" required />
                                </div>
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1 pl-1">Domicile <span class="text-red-500">*</span></label>
                                <input v-model="form.parents_metadata.father_domicile" type="text" class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold" required />
                            </div>
                        </div>
                        <!-- Mère -->
                        <div class="p-6 bg-pink-50/40 rounded-2xl border border-pink-100 space-y-4">
                            <h4 class="text-[10px] font-black text-pink-900 uppercase tracking-widest flex items-center gap-2">
                                <div class="w-1.5 h-1.5 bg-pink-500 rounded-full"></div>
                                Mère
                            </h4>
                            <div>
                                <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1 pl-1">Prénoms &amp; Nom de la Mère <span class="text-red-500">*</span></label>
                                <input v-model="form.mother_name" type="text" class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold" required />
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1 pl-1">Profession <span class="text-red-500">*</span></label>
                                <input v-model="form.parents_metadata.mother_profession" type="text" class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold" required />
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1 pl-1">Date de naissance <span class="text-red-500">*</span></label>
                                    <input v-model="form.parents_metadata.mother_date_of_birth" type="date" class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold" required />
                                </div>
                                <div>
                                    <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1 pl-1">Lieu de naissance <span class="text-red-500">*</span></label>
                                    <input v-model="form.parents_metadata.mother_place_of_birth" type="text" class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold" required />
                                </div>
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1 pl-1">Domicile <span class="text-red-500">*</span></label>
                                <input v-model="form.parents_metadata.mother_domicile" type="text" class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold" required />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 2C: Parents du Défunt (Décès uniquement) -->
                <div v-if="type === 'deces'" class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8">
                    <h3 class="text-xs font-black uppercase tracking-widest mb-6 flex items-center gap-2" style="color: #1E690F;">
                        <UserGroupIcon class="h-4 w-4" />
                        Filiation &amp; Détails Parents du Défunt
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <!-- Père du défunt -->
                        <div class="p-6 bg-blue-50/40 rounded-2xl border border-blue-100 space-y-4">
                            <h4 class="text-[10px] font-black text-blue-900 uppercase tracking-widest flex items-center gap-2">
                                <div class="w-1.5 h-1.5 bg-blue-600 rounded-full"></div>
                                Père du défunt
                            </h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1 pl-1">Prénom du Père <span class="text-red-500">*</span></label>
                                    <input v-model="form.death_metadata.father_first_name" type="text" class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold" required />
                                </div>
                                <div>
                                    <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1 pl-1">Nom du Père <span class="text-red-500">*</span></label>
                                    <input v-model="form.death_metadata.father_last_name" type="text" class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold" required />
                                </div>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1 pl-1">Date de naissance <span class="text-red-500">*</span></label>
                                    <input v-model="form.death_metadata.father_date_of_birth" type="date" class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold" required />
                                </div>
                                <div>
                                    <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1 pl-1">Profession <span class="text-red-500">*</span></label>
                                    <input v-model="form.death_metadata.father_profession" type="text" class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold" required />
                                </div>
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1 pl-1">Domicile <span class="text-red-500">*</span></label>
                                <input v-model="form.death_metadata.father_domicile" type="text" class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold" required />
                            </div>
                        </div>

                        <!-- Mère du défunt -->
                        <div class="p-6 bg-pink-50/40 rounded-2xl border border-pink-100 space-y-4">
                            <h4 class="text-[10px] font-black text-pink-900 uppercase tracking-widest flex items-center gap-2">
                                <div class="w-1.5 h-1.5 bg-pink-500 rounded-full"></div>
                                Mère du défunt
                            </h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1 pl-1">Prénom de la Mère <span class="text-red-500">*</span></label>
                                    <input v-model="form.death_metadata.mother_first_name" type="text" class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold" required />
                                </div>
                                <div>
                                    <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1 pl-1">Nom de la Mère <span class="text-red-500">*</span></label>
                                    <input v-model="form.death_metadata.mother_last_name" type="text" class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold" required />
                                </div>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1 pl-1">Date de naissance <span class="text-red-500">*</span></label>
                                    <input v-model="form.death_metadata.mother_date_of_birth" type="date" class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold" required />
                                </div>
                                <div>
                                    <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1 pl-1">Profession <span class="text-red-500">*</span></label>
                                    <input v-model="form.death_metadata.mother_profession" type="text" class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold" required />
                                </div>
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1 pl-1">Domicile <span class="text-red-500">*</span></label>
                                <input v-model="form.death_metadata.mother_domicile" type="text" class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold" required />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 2B: Parents des Époux (Mariage uniquement) -->
                <div v-if="type === 'mariage'" class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8 space-y-8">
                    <h3 class="text-xs font-black uppercase tracking-widest flex items-center gap-2" style="color: #1E690F;">
                        <UserGroupIcon class="h-4 w-4" />
                        Parents des Époux
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <!-- Parents de l'époux (Mari) -->
                        <div class="space-y-6 p-6 bg-blue-50/20 rounded-3xl border border-blue-100/50">
                            <h4 class="text-xs font-black text-blue-900 uppercase tracking-widest border-b border-blue-100 pb-2">Parents de l'Époux</h4>
                            <!-- Père de l'époux -->
                            <div class="space-y-3">
                                <span class="text-[10px] font-black text-gray-400 uppercase tracking-wider block">Père <span class="text-red-500">*</span></span>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <input v-model="form.spouses_metadata.husband_father_first_name" placeholder="Prénoms *" class="w-full px-4 py-2.5 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold text-sm" required />
                                    <input v-model="form.spouses_metadata.husband_father_last_name" placeholder="Nom *" class="w-full px-4 py-2.5 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold text-sm" required />
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <input v-model="form.spouses_metadata.husband_father_date_of_birth" type="date" placeholder="Date de naissance *" class="w-full px-4 py-2.5 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold text-sm" required />
                                    <input v-model="form.spouses_metadata.husband_father_profession" placeholder="Profession *" class="w-full px-4 py-2.5 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold text-sm" required />
                                </div>
                                <input v-model="form.spouses_metadata.husband_father_domicile" placeholder="Domicile *" class="w-full px-4 py-2.5 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold text-sm" required />
                            </div>
                            
                            <!-- Mère de l'époux -->
                            <div class="space-y-3 pt-4 border-t border-blue-100/50">
                                <span class="text-[10px] font-black text-gray-400 uppercase tracking-wider block">Mère <span class="text-red-500">*</span></span>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <input v-model="form.spouses_metadata.husband_mother_first_name" placeholder="Prénoms *" class="w-full px-4 py-2.5 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold text-sm" required />
                                    <input v-model="form.spouses_metadata.husband_mother_last_name" placeholder="Nom *" class="w-full px-4 py-2.5 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold text-sm" required />
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <input v-model="form.spouses_metadata.husband_mother_date_of_birth" type="date" placeholder="Date de naissance *" class="w-full px-4 py-2.5 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold text-sm" required />
                                    <input v-model="form.spouses_metadata.husband_mother_profession" placeholder="Profession *" class="w-full px-4 py-2.5 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold text-sm" required />
                                </div>
                                <input v-model="form.spouses_metadata.husband_mother_domicile" placeholder="Domicile *" class="w-full px-4 py-2.5 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold text-sm" required />
                            </div>
                        </div>

                        <!-- Parents de l'épouse -->
                        <div class="space-y-6 p-6 bg-pink-50/20 rounded-3xl border border-pink-100/50">
                            <h4 class="text-xs font-black text-pink-900 uppercase tracking-widest border-b border-pink-100 pb-2">Parents de l'Épouse</h4>
                            <!-- Père de l'épouse -->
                            <div class="space-y-3">
                                <span class="text-[10px] font-black text-gray-400 uppercase tracking-wider block">Père <span class="text-red-500">*</span></span>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <input v-model="form.spouses_metadata.wife_father_first_name" placeholder="Prénoms *" class="w-full px-4 py-2.5 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold text-sm" required />
                                    <input v-model="form.spouses_metadata.wife_father_last_name" placeholder="Nom *" class="w-full px-4 py-2.5 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold text-sm" required />
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <input v-model="form.spouses_metadata.wife_father_date_of_birth" type="date" placeholder="Date de naissance *" class="w-full px-4 py-2.5 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold text-sm" required />
                                    <input v-model="form.spouses_metadata.wife_father_profession" placeholder="Profession *" class="w-full px-4 py-2.5 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold text-sm" required />
                                </div>
                                <input v-model="form.spouses_metadata.wife_father_domicile" placeholder="Domicile *" class="w-full px-4 py-2.5 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold text-sm" required />
                            </div>
                            
                            <!-- Mère de l'épouse -->
                            <div class="space-y-3 pt-4 border-t border-pink-100/50">
                                <span class="text-[10px] font-black text-gray-400 uppercase tracking-wider block">Mère <span class="text-red-500">*</span></span>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <input v-model="form.spouses_metadata.wife_mother_first_name" placeholder="Prénoms *" class="w-full px-4 py-2.5 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold text-sm" required />
                                    <input v-model="form.spouses_metadata.wife_mother_last_name" placeholder="Nom *" class="w-full px-4 py-2.5 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold text-sm" required />
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <input v-model="form.spouses_metadata.wife_mother_date_of_birth" type="date" placeholder="Date de naissance *" class="w-full px-4 py-2.5 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold text-sm" required />
                                    <input v-model="form.spouses_metadata.wife_mother_profession" placeholder="Profession *" class="w-full px-4 py-2.5 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold text-sm" required />
                                </div>
                                <input v-model="form.spouses_metadata.wife_mother_domicile" placeholder="Domicile *" class="w-full px-4 py-2.5 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold text-sm" required />
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
                    <!-- Notice par défaut quand pas de déclarant tiers -->
                    <div v-if="!hasDeclarant" class="flex items-start gap-3 p-4 bg-blue-50 rounded-2xl border border-blue-100">
                        <svg class="h-4 w-4 text-blue-500 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="text-[11px] text-blue-700 font-medium leading-relaxed">
                            <span class="font-black">Déclarant par défaut : le Père.</span>
                            Conformément à la règle légale, en l'absence d'un déclarant tiers, c'est le père de l'enfant qui est considéré comme déclarant de la naissance.
                        </p>
                    </div>
                    <div v-if="hasDeclarant" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1 pl-1">Prénoms du Déclarant</label>
                                <input v-model="form.parents_metadata.declarant_first_name" type="text" class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold" />
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1 pl-1">Nom du Déclarant</label>
                                <input v-model="form.parents_metadata.declarant_last_name" type="text" class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold" />
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
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
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
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
                </div>

                <!-- Section Jugement (Naissance) -->
                <div v-if="type === 'naissance'" class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8 space-y-6">
                    <div class="flex items-center gap-2">
                        <input id="is_judgment" v-model="form.is_judgment" type="checkbox" class="h-4 w-4 rounded border-gray-300 text-[#1E690F] focus:ring-[#1E690F] focus:border-[#1E690F]" />
                        <label for="is_judgment" class="text-xs font-black text-gray-700 uppercase tracking-wider cursor-pointer">Cet acte fait suite à un Jugement de Naissance</label>
                    </div>
                    <div v-if="form.is_judgment" class="p-6 bg-green-50/30 rounded-2xl border border-green-100/50 space-y-6">
                        <h4 class="text-[10px] font-black text-[#1E690F] uppercase tracking-widest flex items-center gap-2">
                            <div class="w-1.5 h-1.5 bg-[#1E690F] rounded-full"></div>
                            Références du Jugement & d'Autorisation
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
                                <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1 pl-1">Juridiction (Tribunal)</label>
                                <input v-model="form.judgment_court" type="text" class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold" :required="form.is_judgment" placeholder="Tribunal d'Instance" />
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1 pl-1">Date de l'Autorisation (si distincte)</label>
                                <input v-model="form.parents_metadata.judgment_auth_date" type="date" class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold" />
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1 pl-1">Référence de l'Autorisation</label>
                                <input v-model="form.parents_metadata.judgment_auth_ref" type="text" class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold" placeholder="Réf. Autorisation" />
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1 pl-1">Pièce Justificative (Copie Jugement PDF, max. 500 Ko)</label>
                                <label class="flex flex-col items-center justify-center w-full h-12 border-2 border-dashed rounded-xl cursor-pointer hover:bg-gray-50/50 transition-all" :class="form.doc_jugement ? 'border-[#1E690F] bg-green-50' : 'border-gray-300 bg-white'">
                                    <div class="flex items-center gap-2">
                                        <DocumentIcon class="w-4 h-4" :class="form.doc_jugement ? 'text-[#1E690F]' : 'text-gray-400'" />
                                        <span class="text-xs text-gray-500 font-bold"><span v-if="form.doc_jugement">{{ form.doc_jugement.name }}</span><span v-else>Téléverser le PDF (max. 500 Ko)</span></span>
                                    </div>
                                    <input type="file" class="hidden" accept="application/pdf" @change="(e) => handleFileChange(e, 'doc_jugement')" />
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 3C: Déclarant (Décès uniquement) -->
                <div v-if="type === 'deces'" class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8 space-y-6">
                    <h3 class="text-xs font-black uppercase tracking-widest flex items-center gap-2" style="color: #1E690F;">
                        <UserIcon class="h-4 w-4" />
                        Déclarant du Décès
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1 pl-1">Prénoms du Déclarant <span class="text-red-500">*</span></label>
                            <input v-model="form.death_metadata.declarant_first_name" type="text" class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold" required />
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1 pl-1">Nom du Déclarant <span class="text-red-500">*</span></label>
                            <input v-model="form.death_metadata.declarant_last_name" type="text" class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold" required />
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1 pl-1">Profession <span class="text-red-500">*</span></label>
                            <input v-model="form.death_metadata.declarant_profession" type="text" class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold" required />
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1 pl-1">Numéro d'Identification (CNI) <span class="text-red-500">*</span></label>
                            <input v-model="form.death_metadata.declarant_id_number" type="text" class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold" required />
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1 pl-1">Adresse du Déclarant <span class="text-red-500">*</span></label>
                            <input v-model="form.death_metadata.declarant_address" type="text" class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold" required />
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1 pl-1">Degré de parenté / Relation avec le défunt <span class="text-red-500">*</span></label>
                            <input v-model="form.death_metadata.declarant_relationship" type="text" class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold" placeholder="Ex : Fils, Conjoint, Voisin..." required />
                        </div>
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1 pl-1">Date et heure de la déclaration <span class="text-red-500">*</span></label>
                        <input v-model="form.death_metadata.declarant_date_time" type="datetime-local" class="w-full md:w-72 px-4 py-3 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold" required />
                    </div>
                </div>

                <!-- Témoins (Naissance) -->
                <div v-if="type === 'naissance'" class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-xs font-black uppercase tracking-widest flex items-center gap-2" style="color: #1E690F;">
                            <UserGroupIcon class="h-4 w-4" />
                            Témoins (Optionnel, 0 à 2 témoins)
                        </h3>
                        <button v-if="!form.parents_metadata.witnesses || form.parents_metadata.witnesses.length < 2" 
                                type="button" 
                                @click="addWitness" 
                                class="px-4 py-2 bg-green-50 text-[#1E690F] hover:bg-green-100 text-xs font-bold rounded-xl flex items-center gap-1 transition-all">
                            <PlusCircleIcon class="h-4 w-4" />
                            Ajouter un Témoin
                        </button>
                    </div>
                    <div v-if="form.parents_metadata.witnesses && form.parents_metadata.witnesses.length > 0" class="space-y-6">
                        <div v-for="(witness, index) in form.parents_metadata.witnesses" :key="index" class="p-6 bg-gray-50/50 rounded-2xl border border-gray-100 space-y-4">
                            <div class="flex items-center justify-between">
                                <h4 class="text-[10px] font-black text-gray-500 uppercase tracking-widest">Témoin {{ index + 1 }}</h4>
                                <button type="button" @click="removeWitness(index)" class="text-xs font-bold text-red-600 hover:text-red-800 flex items-center gap-1">
                                    Supprimer
                                </button>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1 pl-1">Prénoms</label>
                                    <input v-model="witness.first_name" type="text" class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold" required />
                                </div>
                                <div>
                                    <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1 pl-1">Nom</label>
                                    <input v-model="witness.last_name" type="text" class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold" required />
                                </div>
                                <div>
                                    <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1 pl-1">Date de Naissance</label>
                                    <input v-model="witness.date_of_birth" type="date" class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold" required />
                                </div>
                                <div>
                                    <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1 pl-1">Lieu de Naissance</label>
                                    <input v-model="witness.place_of_birth" type="text" class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold" required />
                                </div>
                                <div>
                                    <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1 pl-1">Profession</label>
                                    <input v-model="witness.profession" type="text" class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold" required />
                                </div>
                                <div>
                                    <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1 pl-1">Numéro d'Identification (CNI ou autre)</label>
                                    <input v-model="witness.id_number" type="text" class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold" required />
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1 pl-1">Adresse</label>
                                    <input v-model="witness.address" type="text" class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold" required />
                                </div>
                            </div>
                        </div>
                    </div>
                    <p v-else class="text-xs text-gray-400 italic">Aucun témoin renseigné pour cet acte.</p>
                </div>



                <div v-if="['mariage', 'deces'].includes(type)" class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8 space-y-6">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xs font-black uppercase tracking-widest flex items-center gap-2" style="color: #1E690F;">
                            <UserGroupIcon class="h-4 w-4" />
                            {{ type === 'mariage' ? 'Témoins du Mariage (0 à 4 témoins)' : 'Témoins du Décès' }}
                        </h3>
                        <button type="button" @click="addMarriageWitness" v-if="!form.witnesses_metadata || (type === 'mariage' ? form.witnesses_metadata.length < 4 : form.witnesses_metadata.length < 10)" class="px-4 py-2 bg-green-50 text-[#1E690F] hover:bg-green-100 rounded-xl text-xs font-black uppercase tracking-widest flex items-center gap-1.5 transition-all">
                            <PlusIcon class="h-4 w-4" /> Ajouter un témoin
                        </button>
                    </div>

                    <div v-if="form.witnesses_metadata && form.witnesses_metadata.length > 0" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div v-for="(witness, index) in form.witnesses_metadata" :key="index" class="p-6 bg-gray-50/50 rounded-2xl border border-gray-100 space-y-4 relative">
                            <button type="button" @click="removeMarriageWitness(index)" class="absolute top-4 right-4 text-red-500 hover:text-red-700 transition-colors">
                                <TrashIcon class="h-4 w-4" />
                            </button>
                            <h4 class="text-[10px] font-black text-gray-400 uppercase tracking-widest flex items-center gap-1.5">
                                <div class="w-1.5 h-1.5 bg-[#1E690F] rounded-full"></div>
                                Témoin {{ index + 1 }}
                            </h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-[9px] font-black text-gray-500 uppercase tracking-widest mb-1 pl-1">Prénom</label>
                                    <input v-model="witness.first_name" type="text" class="w-full px-4 py-2.5 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold text-sm" required />
                                </div>
                                <div>
                                    <label class="block text-[9px] font-black text-gray-500 uppercase tracking-widest mb-1 pl-1">Nom</label>
                                    <input v-model="witness.last_name" type="text" class="w-full px-4 py-2.5 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold text-sm" required />
                                </div>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-[9px] font-black text-gray-500 uppercase tracking-widest mb-1 pl-1">Profession</label>
                                    <input v-model="witness.profession" type="text" class="w-full px-4 py-2.5 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold text-sm" required />
                                </div>
                                <div>
                                    <label class="block text-[9px] font-black text-gray-500 uppercase tracking-widest mb-1 pl-1">N° CNI (ID)</label>
                                    <input v-model="witness.id_number" type="text" class="w-full px-4 py-2.5 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold text-sm" required />
                                </div>
                            </div>
                            <div>
                                <label class="block text-[9px] font-black text-gray-500 uppercase tracking-widest mb-1 pl-1">Domicile (Adresse)</label>
                                <input v-model="witness.address" type="text" class="w-full px-4 py-2.5 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold text-sm" required />
                            </div>
                            <!-- Upload CNI Témoin -->
                            <div>
                                <label class="block text-[9px] font-black text-gray-500 uppercase tracking-widest mb-1 pl-1">Copie CNI (PDF, max. 500 Ko)</label>
                                <label class="flex flex-col items-center justify-center w-full h-12 border-2 border-dashed rounded-xl cursor-pointer hover:bg-gray-50/50 transition-all" :class="witness.cni_file || witness.doc_cni_path ? 'border-[#1E690F] bg-green-50' : 'border-gray-300 bg-white'">
                                    <div class="flex items-center gap-2">
                                        <DocumentIcon class="w-4 h-4" :class="witness.cni_file || witness.doc_cni_path ? 'text-[#1E690F]' : 'text-gray-400'" />
                                        <span class="text-xs text-gray-500 font-bold">
                                            <span v-if="witness.cni_file">{{ witness.cni_file.name }}</span>
                                            <span v-else-if="witness.doc_cni_path">CNI Déjà téléversée (Modifier)</span>
                                            <span v-else>Téléverser la CNI (PDF, max. 500 Ko)</span>
                                        </span>
                                    </div>
                                    <input type="file" class="hidden" accept="application/pdf" @change="(e) => handleFileChange(e, witness, 'cni_file')" />
                                </label>
                                <div v-if="witness.doc_cni_path && !witness.cni_file" class="text-[9px] text-[#1E690F] font-bold mt-1">
                                    <a :href="witness.doc_cni_path" target="_blank" class="hover:underline">Visualiser le document existant</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <p v-else class="text-xs text-gray-400 italic">Aucun témoin renseigné pour cet acte.</p>
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
                            <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-2 pl-1">CNI du Père (PDF, max. 500 Ko) <span v-if="!props.act && !form.is_old_registry" class="text-red-500">*</span><span v-if="!props.act && form.is_old_registry" class="text-amber-500 text-[9px] font-bold ml-1">(Optionnel)</span></label>
                            <label class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed rounded-2xl cursor-pointer hover:bg-gray-50/50 transition-all" :class="form.doc_cni_pere || props.act?.doc_cni_pere_path ? 'border-[#1E690F] bg-green-50' : 'border-gray-300 bg-white'">
                                <DocumentIcon class="w-6 h-6 mb-1" :class="form.doc_cni_pere || props.act?.doc_cni_pere_path ? 'text-[#1E690F]' : 'text-gray-400'" />
                                <p class="text-xs text-gray-500 font-bold text-center px-2">
                                    <span v-if="form.doc_cni_pere">{{ form.doc_cni_pere.name }}</span>
                                    <span v-else-if="props.act?.doc_cni_pere_path">Document existant (Modifier)</span>
                                    <span v-else>Cliquer pour téléverser (PDF, max. 500 Ko)</span>
                                </p>
                                <input type="file" class="hidden" accept="application/pdf" @change="(e) => handleFileChange(e, 'doc_cni_pere')" />
                            </label>
                            <div v-if="props.act?.doc_cni_pere_path && !form.doc_cni_pere" class="text-[9px] text-[#1E690F] font-bold mt-1 text-center">
                                <a :href="props.act.doc_cni_pere_path" target="_blank" class="hover:underline">Visualiser le document existant</a>
                            </div>
                        </div>
                        <!-- CNI Mère -->
                        <div>
                            <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-2 pl-1">CNI de la Mère (PDF, max. 500 Ko) <span v-if="!props.act && !form.is_old_registry" class="text-red-500">*</span><span v-if="!props.act && form.is_old_registry" class="text-amber-500 text-[9px] font-bold ml-1">(Optionnel)</span></label>
                            <label class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed rounded-2xl cursor-pointer hover:bg-gray-50/50 transition-all" :class="form.doc_cni_mere || props.act?.doc_cni_mere_path ? 'border-[#1E690F] bg-green-50' : 'border-gray-300 bg-white'">
                                <DocumentIcon class="w-6 h-6 mb-1" :class="form.doc_cni_mere || props.act?.doc_cni_mere_path ? 'text-[#1E690F]' : 'text-gray-400'" />
                                <p class="text-xs text-gray-500 font-bold text-center px-2">
                                    <span v-if="form.doc_cni_mere">{{ form.doc_cni_mere.name }}</span>
                                    <span v-else-if="props.act?.doc_cni_mere_path">Document existant (Modifier)</span>
                                    <span v-else>Cliquer pour téléverser (PDF, max. 500 Ko)</span>
                                </p>
                                <input type="file" class="hidden" accept="application/pdf" @change="(e) => handleFileChange(e, 'doc_cni_mere')" />
                            </label>
                            <div v-if="props.act?.doc_cni_mere_path && !form.doc_cni_mere" class="text-[9px] text-[#1E690F] font-bold mt-1 text-center">
                                <a :href="props.act.doc_cni_mere_path" target="_blank" class="hover:underline">Visualiser le document existant</a>
                            </div>
                        </div>
                        <!-- Acte / Attestation de naissance -->
                        <div>
                            <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-2 pl-1">Acte / Attestation de Naissance (PDF, max. 500 Ko) <span v-if="!props.act && !form.is_old_registry" class="text-red-500">*</span><span v-if="!props.act && form.is_old_registry" class="text-amber-500 text-[9px] font-bold ml-1">(Optionnel)</span></label>
                            <label class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed rounded-2xl cursor-pointer hover:bg-gray-50/50 transition-all" :class="form.doc_acte_naissance || props.act?.doc_acte_naissance_path ? 'border-[#1E690F] bg-green-50' : 'border-gray-300 bg-white'">
                                <DocumentIcon class="w-6 h-6 mb-1" :class="form.doc_acte_naissance || props.act?.doc_acte_naissance_path ? 'text-[#1E690F]' : 'text-gray-400'" />
                                <p class="text-xs text-gray-500 font-bold text-center px-2">
                                    <span v-if="form.doc_acte_naissance">{{ form.doc_acte_naissance.name }}</span>
                                    <span v-else-if="props.act?.doc_acte_naissance_path">Document existant (Modifier)</span>
                                    <span v-else>Cliquer pour téléverser (PDF, max. 500 Ko)</span>
                                </p>
                                <input type="file" class="hidden" accept="application/pdf" @change="(e) => handleFileChange(e, 'doc_acte_naissance')" />
                            </label>
                            <div v-if="props.act?.doc_acte_naissance_path && !form.doc_acte_naissance" class="text-[9px] text-[#1E690F] font-bold mt-1 text-center">
                                <a :href="props.act.doc_acte_naissance_path" target="_blank" class="hover:underline">Visualiser le document existant</a>
                            </div>
                        </div>
                        <!-- CNI Déclarant -->
                        <div>
                            <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-2 pl-1">CNI du Déclarant (PDF, max. 500 Ko) <span v-if="!props.act && !form.is_old_registry" class="text-red-500">*</span><span v-if="!props.act && form.is_old_registry" class="text-amber-500 text-[9px] font-bold ml-1">(Optionnel)</span></label>
                            <label class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed rounded-2xl cursor-pointer hover:bg-gray-50/50 transition-all" :class="form.doc_cni_declarant || props.act?.doc_cni_declarant_path ? 'border-[#1E690F] bg-green-50' : 'border-gray-300 bg-white'">
                                <DocumentIcon class="w-6 h-6 mb-1" :class="form.doc_cni_declarant || props.act?.doc_cni_declarant_path ? 'text-[#1E690F]' : 'text-gray-400'" />
                                <p class="text-xs text-gray-500 font-bold text-center px-2">
                                    <span v-if="form.doc_cni_declarant">{{ form.doc_cni_declarant.name }}</span>
                                    <span v-else-if="props.act?.doc_cni_declarant_path">Document existant (Modifier)</span>
                                    <span v-else>Cliquer pour téléverser (PDF, max. 500 Ko)</span>
                                </p>
                                <input type="file" class="hidden" accept="application/pdf" @change="(e) => handleFileChange(e, 'doc_cni_declarant')" />
                            </label>
                            <div v-if="props.act?.doc_cni_declarant_path && !form.doc_cni_declarant" class="text-[9px] text-[#1E690F] font-bold mt-1 text-center">
                                <a :href="props.act.doc_cni_declarant_path" target="_blank" class="hover:underline">Visualiser le document existant</a>
                            </div>
                        </div>
                        <!-- Autres pièces -->
                        <div class="md:col-span-2">
                            <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-2 pl-1">Autres Pièces Justificatives (PDF, max. 500 Ko)</label>
                            <label class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed rounded-2xl cursor-pointer hover:bg-gray-50/50 transition-all" :class="form.doc_autres || props.act?.doc_autres_path ? 'border-[#1E690F] bg-green-50' : 'border-gray-300 bg-white'">
                                <DocumentIcon class="w-6 h-6 mb-1" :class="form.doc_autres || props.act?.doc_autres_path ? 'text-[#1E690F]' : 'text-gray-400'" />
                                <p class="text-xs text-gray-500 font-bold text-center px-2">
                                    <span v-if="form.doc_autres">{{ form.doc_autres.name }}</span>
                                    <span v-else-if="props.act?.doc_autres_path">Document existant (Modifier)</span>
                                    <span v-else>Cliquer pour téléverser (PDF, max. 500 Ko)</span>
                                </p>
                                <input type="file" class="hidden" accept="application/pdf" @change="(e) => handleFileChange(e, 'doc_autres')" />
                            </label>
                            <div v-if="props.act?.doc_autres_path && !form.doc_autres" class="text-[9px] text-[#1E690F] font-bold mt-1 text-center">
                                <a :href="props.act.doc_autres_path" target="_blank" class="hover:underline">Visualiser le document existant</a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pièces justificatives — Mariage (PDFs par catégorie) -->
                <div v-if="type === 'mariage'" class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8">
                    <h3 class="text-xs font-black uppercase tracking-widest mb-6 flex items-center gap-2" style="color: #1E690F;">
                        <DocumentArrowUpIcon class="h-4 w-4" />
                        Pièces Justificatives (PDF par catégorie)
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <!-- CNI Époux -->
                        <div>
                            <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-2 pl-1">CNI de l'Époux (PDF, max. 500 Ko) <span v-if="!props.act && !form.is_old_registry" class="text-red-500">*</span><span v-if="!props.act && form.is_old_registry" class="text-amber-500 text-[9px] font-bold ml-1">(Optionnel)</span></label>
                            <label class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed rounded-2xl cursor-pointer hover:bg-gray-50/50 transition-all" :class="form.doc_cni_husband || props.act?.doc_cni_husband_path ? 'border-[#1E690F] bg-green-50' : 'border-gray-300 bg-white'">
                                <DocumentIcon class="w-6 h-6 mb-1" :class="form.doc_cni_husband || props.act?.doc_cni_husband_path ? 'text-[#1E690F]' : 'text-gray-400'" />
                                <p class="text-xs text-gray-500 font-bold text-center px-2">
                                    <span v-if="form.doc_cni_husband">{{ form.doc_cni_husband.name }}</span>
                                    <span v-else-if="props.act?.doc_cni_husband_path">Document existant (Modifier)</span>
                                    <span v-else>Cliquer pour téléverser (PDF, max. 500 Ko)</span>
                                </p>
                                <input type="file" class="hidden" accept="application/pdf" @change="(e) => handleFileChange(e, 'doc_cni_husband')" />
                            </label>
                            <div v-if="props.act?.doc_cni_husband_path && !form.doc_cni_husband" class="text-[9px] text-[#1E690F] font-bold mt-1 text-center">
                                <a :href="props.act.doc_cni_husband_path" target="_blank" class="hover:underline">Visualiser le document existant</a>
                            </div>
                        </div>
                        
                        <!-- CNI Épouse -->
                        <div>
                            <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-2 pl-1">CNI de l'Épouse (PDF, max. 500 Ko) <span v-if="!props.act && !form.is_old_registry" class="text-red-500">*</span><span v-if="!props.act && form.is_old_registry" class="text-amber-500 text-[9px] font-bold ml-1">(Optionnel)</span></label>
                            <label class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed rounded-2xl cursor-pointer hover:bg-gray-50/50 transition-all" :class="form.doc_cni_wife || props.act?.doc_cni_wife_path ? 'border-[#1E690F] bg-green-50' : 'border-gray-300 bg-white'">
                                <DocumentIcon class="w-6 h-6 mb-1" :class="form.doc_cni_wife || props.act?.doc_cni_wife_path ? 'text-[#1E690F]' : 'text-gray-400'" />
                                <p class="text-xs text-gray-500 font-bold text-center px-2">
                                    <span v-if="form.doc_cni_wife">{{ form.doc_cni_wife.name }}</span>
                                    <span v-else-if="props.act?.doc_cni_wife_path">Document existant (Modifier)</span>
                                    <span v-else>Cliquer pour téléverser (PDF, max. 500 Ko)</span>
                                </p>
                                <input type="file" class="hidden" accept="application/pdf" @change="(e) => handleFileChange(e, 'doc_cni_wife')" />
                            </label>
                            <div v-if="props.act?.doc_cni_wife_path && !form.doc_cni_wife" class="text-[9px] text-[#1E690F] font-bold mt-1 text-center">
                                <a :href="props.act.doc_cni_wife_path" target="_blank" class="hover:underline">Visualiser le document existant</a>
                            </div>
                        </div>

                        <!-- Acte de Naissance Époux -->
                        <div>
                            <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-2 pl-1">Acte de Naissance de l'Époux (PDF, max. 500 Ko) <span v-if="!props.act && !form.is_old_registry" class="text-red-500">*</span><span v-if="!props.act && form.is_old_registry" class="text-amber-500 text-[9px] font-bold ml-1">(Optionnel)</span></label>
                            <label class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed rounded-2xl cursor-pointer hover:bg-gray-50/50 transition-all" :class="form.doc_birth_husband || props.act?.doc_birth_husband_path ? 'border-[#1E690F] bg-green-50' : 'border-gray-300 bg-white'">
                                <DocumentIcon class="w-6 h-6 mb-1" :class="form.doc_birth_husband || props.act?.doc_birth_husband_path ? 'text-[#1E690F]' : 'text-gray-400'" />
                                <p class="text-xs text-gray-500 font-bold text-center px-2">
                                    <span v-if="form.doc_birth_husband">{{ form.doc_birth_husband.name }}</span>
                                    <span v-else-if="props.act?.doc_birth_husband_path">Document existant (Modifier)</span>
                                    <span v-else>Cliquer pour téléverser (PDF, max. 500 Ko)</span>
                                </p>
                                <input type="file" class="hidden" accept="application/pdf" @change="(e) => handleFileChange(e, 'doc_birth_husband')" />
                            </label>
                            <div v-if="props.act?.doc_birth_husband_path && !form.doc_birth_husband" class="text-[9px] text-[#1E690F] font-bold mt-1 text-center">
                                <a :href="props.act.doc_birth_husband_path" target="_blank" class="hover:underline">Visualiser le document existant</a>
                            </div>
                        </div>

                        <!-- Acte de Naissance Épouse -->
                        <div>
                            <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-2 pl-1">Acte de Naissance de l'Épouse (PDF, max. 500 Ko) <span v-if="!props.act && !form.is_old_registry" class="text-red-500">*</span><span v-if="!props.act && form.is_old_registry" class="text-amber-500 text-[9px] font-bold ml-1">(Optionnel)</span></label>
                            <label class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed rounded-2xl cursor-pointer hover:bg-gray-50/50 transition-all" :class="form.doc_birth_wife || props.act?.doc_birth_wife_path ? 'border-[#1E690F] bg-green-50' : 'border-gray-300 bg-white'">
                                <DocumentIcon class="w-6 h-6 mb-1" :class="form.doc_birth_wife || props.act?.doc_birth_wife_path ? 'text-[#1E690F]' : 'text-gray-400'" />
                                <p class="text-xs text-gray-500 font-bold text-center px-2">
                                    <span v-if="form.doc_birth_wife">{{ form.doc_birth_wife.name }}</span>
                                    <span v-else-if="props.act?.doc_birth_wife_path">Document existant (Modifier)</span>
                                    <span v-else>Cliquer pour téléverser (PDF, max. 500 Ko)</span>
                                </p>
                                <input type="file" class="hidden" accept="application/pdf" @change="(e) => handleFileChange(e, 'doc_birth_wife')" />
                            </label>
                            <div v-if="props.act?.doc_birth_wife_path && !form.doc_birth_wife" class="text-[9px] text-[#1E690F] font-bold mt-1 text-center">
                                <a :href="props.act.doc_birth_wife_path" target="_blank" class="hover:underline">Visualiser le document existant</a>
                            </div>
                        </div>

                        <!-- Certificat de Domicile -->
                        <div>
                            <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-2 pl-1">Certificat de Domicile (PDF, max. 500 Ko) <span v-if="!props.act && !form.is_old_registry" class="text-red-500">*</span><span v-if="!props.act && form.is_old_registry" class="text-amber-500 text-[9px] font-bold ml-1">(Optionnel)</span></label>
                            <label class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed rounded-2xl cursor-pointer hover:bg-gray-50/50 transition-all" :class="form.doc_domicile || props.act?.doc_domicile_path ? 'border-[#1E690F] bg-green-50' : 'border-gray-300 bg-white'">
                                <DocumentIcon class="w-6 h-6 mb-1" :class="form.doc_domicile || props.act?.doc_domicile_path ? 'text-[#1E690F]' : 'text-gray-400'" />
                                <p class="text-xs text-gray-500 font-bold text-center px-2">
                                    <span v-if="form.doc_domicile">{{ form.doc_domicile.name }}</span>
                                    <span v-else-if="props.act?.doc_domicile_path">Document existant (Modifier)</span>
                                    <span v-else>Cliquer pour téléverser (PDF, max. 500 Ko)</span>
                                </p>
                                <input type="file" class="hidden" accept="application/pdf" @change="(e) => handleFileChange(e, 'doc_domicile')" />
                            </label>
                            <div v-if="props.act?.doc_domicile_path && !form.doc_domicile" class="text-[9px] text-[#1E690F] font-bold mt-1 text-center">
                                <a :href="props.act.doc_domicile_path" target="_blank" class="hover:underline">Visualiser le document existant</a>
                            </div>
                        </div>

                        <!-- Certificat Médical Prénuptial -->
                        <div>
                            <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-2 pl-1">Certificat Médical Prénuptial (PDF, max. 500 Ko) <span v-if="!props.act && !form.is_old_registry" class="text-red-500">*</span><span v-if="!props.act && form.is_old_registry" class="text-amber-500 text-[9px] font-bold ml-1">(Optionnel)</span></label>
                            <label class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed rounded-2xl cursor-pointer hover:bg-gray-50/50 transition-all" :class="form.doc_medical || props.act?.doc_medical_path ? 'border-[#1E690F] bg-green-50' : 'border-gray-300 bg-white'">
                                <DocumentIcon class="w-6 h-6 mb-1" :class="form.doc_medical || props.act?.doc_medical_path ? 'text-[#1E690F]' : 'text-gray-400'" />
                                <p class="text-xs text-gray-500 font-bold text-center px-2">
                                    <span v-if="form.doc_medical">{{ form.doc_medical.name }}</span>
                                    <span v-else-if="props.act?.doc_medical_path">Document existant (Modifier)</span>
                                    <span v-else>Cliquer pour téléverser (PDF, max. 500 Ko)</span>
                                </p>
                                <input type="file" class="hidden" accept="application/pdf" @change="(e) => handleFileChange(e, 'doc_medical')" />
                            </label>
                            <div v-if="props.act?.doc_medical_path && !form.doc_medical" class="text-[9px] text-[#1E690F] font-bold mt-1 text-center">
                                <a :href="props.act.doc_medical_path" target="_blank" class="hover:underline">Visualiser le document existant</a>
                            </div>
                        </div>

                        <!-- Autorisation parentale -->
                        <div>
                            <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-2 pl-1">Autorisation parentale (si mineur, PDF, max. 500 Ko)</label>
                            <label class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed rounded-2xl cursor-pointer hover:bg-gray-50/50 transition-all" :class="form.doc_parental_auth || props.act?.doc_parental_auth_path ? 'border-[#1E690F] bg-green-50' : 'border-gray-300 bg-white'">
                                <DocumentIcon class="w-6 h-6 mb-1" :class="form.doc_parental_auth || props.act?.doc_parental_auth_path ? 'text-[#1E690F]' : 'text-gray-400'" />
                                <p class="text-xs text-gray-500 font-bold text-center px-2">
                                    <span v-if="form.doc_parental_auth">{{ form.doc_parental_auth.name }}</span>
                                    <span v-else-if="props.act?.doc_parental_auth_path">Document existant (Modifier)</span>
                                    <span v-else>Cliquer pour téléverser (PDF, max. 500 Ko)</span>
                                </p>
                                <input type="file" class="hidden" accept="application/pdf" @change="(e) => handleFileChange(e, 'doc_parental_auth')" />
                            </label>
                            <div v-if="props.act?.doc_parental_auth_path && !form.doc_parental_auth" class="text-[9px] text-[#1E690F] font-bold mt-1 text-center">
                                <a :href="props.act.doc_parental_auth_path" target="_blank" class="hover:underline">Visualiser le document existant</a>
                            </div>
                        </div>

                        <!-- Autres pièces -->
                        <div class="md:col-span-2 lg:col-span-2">
                            <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-2 pl-1">Autres Pièces Justificatives (PDF, max. 500 Ko)</label>
                            <label class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed rounded-2xl cursor-pointer hover:bg-gray-50/50 transition-all" :class="form.doc_autres || props.act?.doc_autres_path ? 'border-[#1E690F] bg-green-50' : 'border-gray-300 bg-white'">
                                <DocumentIcon class="w-6 h-6 mb-1" :class="form.doc_autres || props.act?.doc_autres_path ? 'text-[#1E690F]' : 'text-gray-400'" />
                                <p class="text-xs text-gray-500 font-bold text-center px-2">
                                    <span v-if="form.doc_autres">{{ form.doc_autres.name }}</span>
                                    <span v-else-if="props.act?.doc_autres_path">Document existant (Modifier)</span>
                                    <span v-else>Cliquer pour téléverser (PDF, max. 500 Ko)</span>
                                </p>
                                <input type="file" class="hidden" accept="application/pdf" @change="(e) => handleFileChange(e, 'doc_autres')" />
                            </label>
                            <div v-if="props.act?.doc_autres_path && !form.doc_autres" class="text-[9px] text-[#1E690F] font-bold mt-1 text-center">
                                <a :href="props.act.doc_autres_path" target="_blank" class="hover:underline">Visualiser le document existant</a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pièces justificatives — Décès (PDFs par catégorie) -->
                <div v-if="type === 'deces'" class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8">
                    <h3 class="text-xs font-black uppercase tracking-widest mb-6 flex items-center gap-2" style="color: #1E690F;">
                        <DocumentArrowUpIcon class="h-4 w-4" />
                        Pièces Justificatives (PDF par catégorie)
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <!-- Certificat de Décès -->
                        <div>
                            <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-2 pl-1">Certificat de Décès / Médical (PDF, max. 500 Ko) <span v-if="!props.act && !form.is_old_registry" class="text-red-500">*</span><span v-if="!props.act && form.is_old_registry" class="text-amber-500 text-[9px] font-bold ml-1">(Optionnel)</span></label>
                            <label class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed rounded-2xl cursor-pointer hover:bg-gray-50/50 transition-all" :class="form.doc_death_cert || props.act?.doc_death_cert_path ? 'border-[#1E690F] bg-green-50' : 'border-gray-300 bg-white'">
                                <DocumentIcon class="w-6 h-6 mb-1" :class="form.doc_death_cert || props.act?.doc_death_cert_path ? 'text-[#1E690F]' : 'text-gray-400'" />
                                <p class="text-xs text-gray-500 font-bold text-center px-2">
                                    <span v-if="form.doc_death_cert">{{ form.doc_death_cert.name }}</span>
                                    <span v-else-if="props.act?.doc_death_cert_path">Document existant (Modifier)</span>
                                    <span v-else>Cliquer pour téléverser (PDF, max. 500 Ko)</span>
                                </p>
                                <input type="file" class="hidden" accept="application/pdf" @change="(e) => handleFileChange(e, 'doc_death_cert')" />
                            </label>
                            <div v-if="props.act?.doc_death_cert_path && !form.doc_death_cert" class="text-[9px] text-[#1E690F] font-bold mt-1 text-center">
                                <a :href="props.act.doc_death_cert_path" target="_blank" class="hover:underline">Visualiser le document existant</a>
                            </div>
                        </div>

                        <!-- Pièce d'identité du défunt -->
                        <div>
                            <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-2 pl-1">Pièce d'identité du défunt (PDF, max. 500 Ko) <span v-if="!props.act && !form.is_old_registry" class="text-red-500">*</span><span v-if="!props.act && form.is_old_registry" class="text-amber-500 text-[9px] font-bold ml-1">(Optionnel)</span></label>
                            <label class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed rounded-2xl cursor-pointer hover:bg-gray-50/50 transition-all" :class="form.doc_deceased_id || props.act?.doc_deceased_id_path ? 'border-[#1E690F] bg-green-50' : 'border-gray-300 bg-white'">
                                <DocumentIcon class="w-6 h-6 mb-1" :class="form.doc_deceased_id || props.act?.doc_deceased_id_path ? 'text-[#1E690F]' : 'text-gray-400'" />
                                <p class="text-xs text-gray-500 font-bold text-center px-2">
                                    <span v-if="form.doc_deceased_id">{{ form.doc_deceased_id.name }}</span>
                                    <span v-else-if="props.act?.doc_deceased_id_path">Document existant (Modifier)</span>
                                    <span v-else>Cliquer pour téléverser (PDF, max. 500 Ko)</span>
                                </p>
                                <input type="file" class="hidden" accept="application/pdf" @change="(e) => handleFileChange(e, 'doc_deceased_id')" />
                            </label>
                            <div v-if="props.act?.doc_deceased_id_path && !form.doc_deceased_id" class="text-[9px] text-[#1E690F] font-bold mt-1 text-center">
                                <a :href="props.act.doc_deceased_id_path" target="_blank" class="hover:underline">Visualiser le document existant</a>
                            </div>
                        </div>

                        <!-- CNI du Déclarant -->
                        <div>
                            <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-2 pl-1">CNI du Déclarant (PDF, max. 500 Ko) <span v-if="!props.act && !form.is_old_registry" class="text-red-500">*</span><span v-if="!props.act && form.is_old_registry" class="text-amber-500 text-[9px] font-bold ml-1">(Optionnel)</span></label>
                            <label class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed rounded-2xl cursor-pointer hover:bg-gray-50/50 transition-all" :class="form.doc_declarant_id || props.act?.doc_declarant_id_path ? 'border-[#1E690F] bg-green-50' : 'border-gray-300 bg-white'">
                                <DocumentIcon class="w-6 h-6 mb-1" :class="form.doc_declarant_id || props.act?.doc_declarant_id_path ? 'text-[#1E690F]' : 'text-gray-400'" />
                                <p class="text-xs text-gray-500 font-bold text-center px-2">
                                    <span v-if="form.doc_declarant_id">{{ form.doc_declarant_id.name }}</span>
                                    <span v-else-if="props.act?.doc_declarant_id_path">Document existant (Modifier)</span>
                                    <span v-else>Cliquer pour téléverser (PDF, max. 500 Ko)</span>
                                </p>
                                <input type="file" class="hidden" accept="application/pdf" @change="(e) => handleFileChange(e, 'doc_declarant_id')" />
                            </label>
                            <div v-if="props.act?.doc_declarant_id_path && !form.doc_declarant_id" class="text-[9px] text-[#1E690F] font-bold mt-1 text-center">
                                <a :href="props.act.doc_declarant_id_path" target="_blank" class="hover:underline">Visualiser le document existant</a>
                            </div>
                        </div>

                        <!-- Copie du Jugement -->
                        <div>
                            <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-2 pl-1">Copie du Jugement (PDF, max. 500 Ko)</label>
                            <label class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed rounded-2xl cursor-pointer hover:bg-gray-50/50 transition-all" :class="form.doc_jugement || props.act?.doc_jugement_path ? 'border-[#1E690F] bg-green-50' : 'border-gray-300 bg-white'">
                                <DocumentIcon class="w-6 h-6 mb-1" :class="form.doc_jugement || props.act?.doc_jugement_path ? 'text-[#1E690F]' : 'text-gray-400'" />
                                <p class="text-xs text-gray-500 font-bold text-center px-2">
                                    <span v-if="form.doc_jugement">{{ form.doc_jugement.name }}</span>
                                    <span v-else-if="props.act?.doc_jugement_path">Document existant (Modifier)</span>
                                    <span v-else>Cliquer pour téléverser (PDF, max. 500 Ko)</span>
                                </p>
                                <input type="file" class="hidden" accept="application/pdf" @change="(e) => handleFileChange(e, 'doc_jugement')" />
                            </label>
                            <div v-if="props.act?.doc_jugement_path && !form.doc_jugement" class="text-[9px] text-[#1E690F] font-bold mt-1 text-center">
                                <a :href="props.act.doc_jugement_path" target="_blank" class="hover:underline">Visualiser le document existant</a>
                            </div>
                        </div>

                        <!-- Autres pièces -->
                        <div class="md:col-span-2">
                            <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-2 pl-1">Autres Pièces Justificatives (PDF, max. 500 Ko)</label>
                            <label class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed rounded-2xl cursor-pointer hover:bg-gray-50/50 transition-all" :class="form.doc_autres || props.act?.doc_autres_path ? 'border-[#1E690F] bg-green-50' : 'border-gray-300 bg-white'">
                                <DocumentIcon class="w-6 h-6 mb-1" :class="form.doc_autres || props.act?.doc_autres_path ? 'text-[#1E690F]' : 'text-gray-400'" />
                                <p class="text-xs text-gray-500 font-bold text-center px-2">
                                    <span v-if="form.doc_autres">{{ form.doc_autres.name }}</span>
                                    <span v-else-if="props.act?.doc_autres_path">Document existant (Modifier)</span>
                                    <span v-else>Cliquer pour téléverser (PDF, max. 500 Ko)</span>
                                </p>
                                <input type="file" class="hidden" accept="application/pdf" @change="(e) => handleFileChange(e, 'doc_autres')" />
                            </label>
                            <div v-if="props.act?.doc_autres_path && !form.doc_autres" class="text-[9px] text-[#1E690F] font-bold mt-1 text-center">
                                <a :href="props.act.doc_autres_path" target="_blank" class="hover:underline">Visualiser le document existant</a>
                            </div>
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
                        <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1 pl-1">Fait à Enampore, le <span class="text-red-500">*</span></label>
                        <input v-model="form.act_registration_date" type="date" class="w-full md:w-72 px-4 py-3 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold" required />
                    </div>
                </div>

                <!-- Mentions marginales / Observations -->
                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8">
                    <h3 class="text-xs font-black uppercase tracking-widest mb-6 flex items-center gap-2" style="color: #1E690F;">
                        <PlusCircleIcon class="h-4 w-4" />
                        {{ ['naissance', 'mariage', 'deces'].includes(type) ? 'Mentions Marginales' : "Observations de l'Officier" }}
                    </h3>
                    <div>
                        <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1 pl-1">
                            {{ ['naissance', 'mariage', 'deces'].includes(type) ? 'Mentions marginales' : 'Commentaires officiels' }}
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

        <!-- Custom Error Modal -->
        <Teleport to="body">
          <Transition
            enter-active-class="transition duration-300 ease-out"
            enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100"
            leave-active-class="transition duration-200 ease-in"
            leave-from-class="opacity-100 scale-100"
            leave-to-class="opacity-0 scale-95"
          >
            <div v-if="showErrorModal" class="fixed inset-0 z-50 overflow-y-auto bg-gray-950/70 backdrop-blur-md flex items-center justify-center p-4">
              <div 
                class="bg-white rounded-[32px] overflow-hidden shadow-2xl transform transition-all max-w-md w-full border border-red-100 p-8 relative"
              >
                <!-- Close Button -->
                <button @click="closeErrorModal" type="button" class="absolute top-6 right-6 text-gray-400 hover:text-gray-600 transition-colors">
                  <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                  </svg>
                </button>

                <div class="flex flex-col items-center text-center">
                  <!-- Warning/Error Icon with pulse effect -->
                  <div class="flex items-center justify-center h-16 w-16 rounded-full bg-red-50 text-red-600 mb-6 animate-pulse">
                    <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                  </div>
                  
                  <h3 class="text-xl font-black text-gray-900 tracking-tight mb-3">
                    {{ modalErrorTitle }}
                  </h3>
                  <p class="text-sm text-gray-500 font-bold leading-relaxed mb-8 px-2">
                    {{ modalErrorMessage }}
                  </p>

                  <button
                    type="button"
                    @click="closeErrorModal"
                    class="w-full py-4 bg-red-600 hover:bg-red-700 text-white rounded-2xl font-black text-xs uppercase tracking-widest shadow-xl shadow-red-200 transition-all active:scale-[0.98] outline-none"
                  >
                    D'accord
                  </button>
                </div>
              </div>
            </div>
          </Transition>
        </Teleport>
    </AuthenticatedLayout>
</template>
