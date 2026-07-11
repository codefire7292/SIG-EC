<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { 
    CalendarIcon, 
    MapPinIcon, 
    IdentificationIcon,
    UserGroupIcon,
    ArrowLeftIcon,
    ClockIcon,
    CheckBadgeIcon,
    PencilSquareIcon,
    PlusCircleIcon,
    DocumentIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    act: Object,
    type: String,
    versions: Array,
});

const title = computed(() => {
    switch (props.type) {
        case 'naissance': return 'Acte de Naissance';
        case 'mariage': return 'Acte de Mariage';
        case 'deces': return 'Acte de Décès';
        default: return 'Acte État-Civil';
    }
});

const formatDate = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('fr-FR', {
        day: '2-digit',
        month: 'long',
        year: 'numeric'
    });
};

import { router, usePage } from '@inertiajs/vue3';

const authUser = usePage().props.auth.user;

const hasRole = (role) => {
    return authUser.roles && authUser.roles.map(r => r.name).includes(role);
};

const updateStatus = (newStatus) => {
    if (confirm(`Confirmez-vous le passage au statut: ${newStatus} ?`)) {
        router.post(`/acts/${props.type}/${props.act.id}/status`, {
            status: newStatus
        }, { preserveScroll: true });
    }
};
</script>

<template>
    <Head :title="title" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <Link :href="`/acts/${type}`" class="p-2 hover:bg-gray-100 rounded-full transition-colors">
                    <ArrowLeftIcon class="h-5 w-5 text-gray-500" />
                </Link>
                <div>
                    <h2 class="font-black text-2xl text-gray-900 tracking-tight">{{ title }}</h2>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">
                        Référence : {{ act.reference_number || 'SANS RÉFÉRENCE' }}
                    </p>
                </div>
            </div>
        </template>

        <div class="max-w-5xl mx-auto space-y-8 pb-20">
            <!-- Main Content Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <!-- Left Column: Primary Details -->
                <div class="lg:col-span-2 space-y-8">
                    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="bg-blue-600 px-8 py-4 flex items-center justify-between">
                            <span class="text-[10px] font-black text-blue-100 uppercase tracking-widest">Détails de l'acte</span>
                            <span class="px-3 py-1 bg-white/20 text-white rounded-full text-[9px] font-black uppercase">
                                Version {{ act.version_number }}
                            </span>
                        </div>
                        
                        <div class="p-8 space-y-8">
                            <!-- Naissance Context -->
                            <div v-if="type === 'naissance'" class="space-y-6">
                                <div class="grid grid-cols-2 gap-8">
                                    <div>
                                        <h4 class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Enfant</h4>
                                        <div class="text-xl font-black text-gray-900">{{ act.first_name }} {{ act.last_name }}</div>
                                        <div class="text-sm font-bold text-blue-600">{{ act.gender === 'M' ? 'Masculin' : 'Féminin' }}</div>
                                    </div>
                                    <div>
                                        <h4 class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Naissance</h4>
                                        <div class="text-sm font-black text-gray-900">{{ formatDate(act.date_of_birth) }}<span v-if="act.time_of_birth" class="text-gray-500 font-medium ml-2">à {{ act.time_of_birth }}</span></div>
                                        <div class="text-xs font-bold text-gray-500 italic">{{ act.place_of_birth }}</div>
                                        <div v-if="act.health_facility" class="text-xs font-bold text-green-700 mt-1">Formation : {{ act.health_facility }}</div>
                                    </div>
                                </div>
                                <div v-if="act.is_judgment" class="p-4 bg-green-50/50 rounded-2xl border border-green-100 flex flex-wrap gap-6 items-center">
                                    <div class="px-3 py-1 bg-[#1E690F] text-white rounded-full text-[9px] font-black uppercase tracking-wider">Jugement de Naissance</div>
                                    <div>
                                        <div class="text-[9px] font-black text-gray-400 uppercase">Numéro du Jugement</div>
                                        <div class="text-xs font-black text-gray-900">{{ act.judgment_number }}</div>
                                    </div>
                                    <div>
                                        <div class="text-[9px] font-black text-gray-400 uppercase">Date du Jugement</div>
                                        <div class="text-xs font-black text-gray-900">{{ formatDate(act.judgment_date) }}</div>
                                    </div>
                                    <div>
                                        <div class="text-[9px] font-black text-gray-400 uppercase">Tribunal</div>
                                        <div class="text-xs font-black text-gray-900">{{ act.judgment_court }}</div>
                                    </div>
                                    <div v-if="act.parents_metadata?.judgment_auth_date">
                                        <div class="text-[9px] font-black text-gray-400 uppercase">Date Autorisation</div>
                                        <div class="text-xs font-black text-gray-900">{{ formatDate(act.parents_metadata.judgment_auth_date) }}</div>
                                    </div>
                                    <div v-if="act.parents_metadata?.judgment_auth_ref">
                                        <div class="text-[9px] font-black text-gray-400 uppercase">Réf. Autorisation</div>
                                        <div class="text-xs font-black text-gray-900">{{ act.parents_metadata.judgment_auth_ref }}</div>
                                    </div>
                                </div>
                                <div class="pt-4 border-t border-gray-50 grid grid-cols-2 gap-8">
                                    <div>
                                        <h4 class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Père</h4>
                                        <div class="text-sm font-bold text-gray-700">{{ act.father_name || 'Non renseigné' }}</div>
                                        <div v-if="act.parents_metadata?.father_date_of_birth" class="text-xs text-gray-500">Né le {{ formatDate(act.parents_metadata.father_date_of_birth) }}<span v-if="act.parents_metadata.father_place_of_birth"> — {{ act.parents_metadata.father_place_of_birth }}</span></div>
                                        <div v-if="act.parents_metadata?.father_domicile" class="text-xs text-gray-500">Domicile : {{ act.parents_metadata.father_domicile }}</div>
                                        <div v-if="act.parents_metadata?.father_profession" class="text-xs text-gray-500">Profession : {{ act.parents_metadata.father_profession }}</div>
                                    </div>
                                    <div>
                                        <h4 class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Mère</h4>
                                        <div class="text-sm font-bold text-gray-700">{{ act.mother_name || 'Non renseignée' }}</div>
                                        <div v-if="act.parents_metadata?.mother_date_of_birth" class="text-xs text-gray-500">Née le {{ formatDate(act.parents_metadata.mother_date_of_birth) }}<span v-if="act.parents_metadata.mother_place_of_birth"> — {{ act.parents_metadata.mother_place_of_birth }}</span></div>
                                        <div v-if="act.parents_metadata?.mother_domicile" class="text-xs text-gray-500">Domicile : {{ act.parents_metadata.mother_domicile }}</div>
                                        <div v-if="act.parents_metadata?.mother_profession" class="text-xs text-gray-500">Profession : {{ act.parents_metadata.mother_profession }}</div>
                                    </div>
                                </div>
                                <!-- Déclarant -->
                                <div v-if="act.parents_metadata?.declarant_first_name || act.parents_metadata?.declarant_last_name" class="pt-4 border-t border-gray-50">
                                    <h4 class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3">Déclarant</h4>
                                    <div class="grid grid-cols-2 gap-4 text-xs">
                                        <div><span class="text-gray-400">Nom :</span> <span class="font-bold text-gray-900">{{ act.parents_metadata.declarant_first_name }} {{ act.parents_metadata.declarant_last_name }}</span></div>
                                        <div v-if="act.parents_metadata.declarant_profession"><span class="text-gray-400">Profession :</span> <span class="font-bold text-gray-900">{{ act.parents_metadata.declarant_profession }}</span></div>
                                        <div v-if="act.parents_metadata.declarant_address"><span class="text-gray-400">Adresse :</span> <span class="font-bold text-gray-900">{{ act.parents_metadata.declarant_address }}</span></div>
                                        <div v-if="act.parents_metadata.declarant_id_number"><span class="text-gray-400">ID :</span> <span class="font-bold text-gray-900">{{ act.parents_metadata.declarant_id_number }}</span></div>
                                        <div v-if="act.parents_metadata.declarant_date"><span class="text-gray-400">Déclaration le :</span> <span class="font-bold text-gray-900">{{ formatDate(act.parents_metadata.declarant_date) }}</span></div>
                                        <div v-if="act.parents_metadata.declarant_judgment_ref"><span class="text-gray-400">Réf. Jugement :</span> <span class="font-bold text-gray-900">{{ act.parents_metadata.declarant_judgment_ref }}</span></div>
                                    </div>
                                </div>
                                <!-- Témoins -->
                                <div v-if="act.parents_metadata?.witnesses && act.parents_metadata.witnesses.length > 0" class="pt-4 border-t border-gray-50">
                                    <h4 class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3">Témoins de l'acte</h4>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div v-for="(witness, index) in act.parents_metadata.witnesses" :key="index" class="p-4 bg-gray-50/50 rounded-2xl border border-gray-100/50 text-xs">
                                            <div class="font-black text-[#1E690F] uppercase tracking-wider mb-2">Témoin {{ index + 1 }}</div>
                                            <div class="space-y-1.5">
                                                <div><span class="text-gray-400">Nom :</span> <span class="font-bold text-gray-900">{{ witness.first_name }} {{ witness.last_name }}</span></div>
                                                <div><span class="text-gray-400">Né(e) le :</span> <span class="font-bold text-gray-900">{{ formatDate(witness.date_of_birth) }}<span v-if="witness.place_of_birth"> à {{ witness.place_of_birth }}</span></span></div>
                                                <div><span class="text-gray-400">Profession :</span> <span class="font-bold text-gray-900">{{ witness.profession }}</span></div>
                                                <div><span class="text-gray-400">Adresse :</span> <span class="font-bold text-gray-900">{{ witness.address }}</span></div>
                                                <div><span class="text-gray-400">N° ID :</span> <span class="font-bold text-gray-900">{{ witness.id_number }}</span></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Mariage Context -->
                            <div v-if="type === 'mariage'" class="space-y-8">
                                <!-- Époux Informations Détaillées -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                    <!-- Époux (Mari) -->
                                    <div class="p-6 bg-blue-50/30 rounded-2xl border border-blue-100/50 space-y-4">
                                        <div class="flex items-center gap-2 border-b border-blue-100 pb-2">
                                            <div class="w-2 h-2 bg-blue-600 rounded-full"></div>
                                            <h4 class="text-xs font-black text-blue-900 uppercase tracking-widest">Épou (Mari)</h4>
                                        </div>
                                        <div class="space-y-2">
                                            <div class="text-xl font-black text-gray-900">{{ act.husband_first_name }} {{ act.husband_last_name }}</div>
                                            <div class="grid grid-cols-2 gap-4 text-xs">
                                                <div>
                                                    <span class="text-gray-400 font-bold block uppercase tracking-wider text-[9px]">Né le</span>
                                                    <span class="text-gray-800 font-black">{{ formatDate(act.spouses_metadata?.husband_date_of_birth) || 'Non renseigné' }}</span>
                                                </div>
                                                <div>
                                                    <span class="text-gray-400 font-bold block uppercase tracking-wider text-[9px]">À</span>
                                                    <span class="text-gray-800 font-bold italic">{{ act.spouses_metadata?.husband_place_of_birth || 'Non renseigné' }}</span>
                                                </div>
                                            </div>
                                            <div class="text-xs pt-1">
                                                <span class="text-gray-400 font-bold block uppercase tracking-wider text-[9px]">Profession</span>
                                                <span class="text-gray-800 font-black">{{ act.spouses_metadata?.husband_profession || 'Non renseigné' }}</span>
                                            </div>
                                            <div class="grid grid-cols-2 gap-4 text-xs pt-1">
                                                <div>
                                                    <span class="text-gray-400 font-bold block uppercase tracking-wider text-[9px]">Domicile</span>
                                                    <span class="text-gray-800 font-bold">{{ act.spouses_metadata?.husband_domicile || 'Non renseigné' }}</span>
                                                </div>
                                                <div>
                                                    <span class="text-gray-400 font-bold block uppercase tracking-wider text-[9px]">Résidence</span>
                                                    <span class="text-gray-800 font-bold">{{ act.spouses_metadata?.husband_residence || 'Non renseigné' }}</span>
                                                </div>
                                            </div>
                                            <div class="text-xs pt-2 border-t border-blue-100/50">
                                                <span class="text-gray-400 font-bold block uppercase tracking-wider text-[9px]">Marié à (épouses existantes)</span>
                                                <span class="text-blue-700 font-black">{{ act.spouses_metadata?.husband_married_to || 'Aucune autre épouse' }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Épouse (Femme) -->
                                    <div class="p-6 bg-pink-50/30 rounded-2xl border border-pink-100/50 space-y-4">
                                        <div class="flex items-center gap-2 border-b border-pink-100 pb-2">
                                            <div class="w-2 h-2 bg-pink-600 rounded-full"></div>
                                            <h4 class="text-xs font-black text-pink-900 uppercase tracking-widest">Épouse (Femme)</h4>
                                        </div>
                                        <div class="space-y-2">
                                            <div class="text-xl font-black text-gray-900">{{ act.wife_first_name }} {{ act.wife_last_name }}</div>
                                            <div class="grid grid-cols-2 gap-4 text-xs">
                                                <div>
                                                    <span class="text-gray-400 font-bold block uppercase tracking-wider text-[9px]">Née le</span>
                                                    <span class="text-gray-800 font-black">{{ formatDate(act.spouses_metadata?.wife_date_of_birth) || 'Non renseignée' }}</span>
                                                </div>
                                                <div>
                                                    <span class="text-gray-400 font-bold block uppercase tracking-wider text-[9px]">À</span>
                                                    <span class="text-gray-800 font-bold italic">{{ act.spouses_metadata?.wife_place_of_birth || 'Non renseignée' }}</span>
                                                </div>
                                            </div>
                                            <div class="text-xs pt-1">
                                                <span class="text-gray-400 font-bold block uppercase tracking-wider text-[9px]">Profession</span>
                                                <span class="text-gray-800 font-black">{{ act.spouses_metadata?.wife_profession || 'Non renseignée' }}</span>
                                            </div>
                                            <div class="grid grid-cols-2 gap-4 text-xs pt-1">
                                                <div>
                                                    <span class="text-gray-400 font-bold block uppercase tracking-wider text-[9px]">Domicile</span>
                                                    <span class="text-gray-800 font-bold">{{ act.spouses_metadata?.wife_domicile || 'Non renseignée' }}</span>
                                                </div>
                                                <div>
                                                    <span class="text-gray-400 font-bold block uppercase tracking-wider text-[9px]">Résidence</span>
                                                    <span class="text-gray-800 font-bold">{{ act.spouses_metadata?.wife_residence || 'Non renseignée' }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Célébration & Régimes -->
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-8 pt-4 border-t border-gray-100">
                                    <div>
                                        <h4 class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Célébration</h4>
                                        <div class="text-sm font-black text-gray-900">{{ formatDate(act.marriage_date) }}</div>
                                    </div>
                                    <div>
                                        <h4 class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Lieu</h4>
                                        <div class="text-sm font-bold text-gray-700 italic">{{ act.marriage_place }}</div>
                                    </div>
                                    <div>
                                        <h4 class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Option Matrimoniale</h4>
                                        <div class="text-sm font-black text-gray-900 uppercase tracking-tight">
                                            {{ act.marriage_option === 'monogamie' ? 'Monogamie' : (act.marriage_option === 'limitation_polygamie' ? 'Limitation de polygamie' : (act.marriage_option === 'polygamie' ? 'Polygamie' : act.marriage_option || 'Polygamie')) }}
                                            <span v-if="act.spouses_metadata?.max_wives" class="text-xs text-yellow-700 font-black block">({{ act.spouses_metadata.max_wives }} épouses max)</span>
                                        </div>
                                    </div>
                                    <div>
                                        <h4 class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Régime Matrimonial</h4>
                                        <div class="text-sm font-black text-[#1E690F] uppercase tracking-tight">
                                            {{ act.matrimonial_regime === 'separation_biens' ? 'Séparation des biens' : (act.matrimonial_regime === 'regime_dotal' ? 'Régime dotal' : (act.matrimonial_regime === 'participation_meubles_acquets' ? 'Participation aux meubles et acquêts' : act.matrimonial_regime || 'Séparation des biens')) }}
                                        </div>
                                    </div>
                                </div>

                                <!-- Jugement d'autorisation -->
                                <div v-if="act.is_judgment || act.judgment_number" class="p-5 bg-gray-50 rounded-2xl border border-gray-200 flex flex-col md:flex-row md:items-center justify-between gap-4">
                                    <div class="space-y-1">
                                        <div class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Déclaration sur Jugement</div>
                                        <div class="text-sm font-black text-gray-900">Jugement d'autorisation N° {{ act.judgment_number }}</div>
                                        <div class="text-xs text-gray-500 font-bold">Rendu le {{ formatDate(act.judgment_date) }}</div>
                                    </div>
                                    <div v-if="act.doc_jugement_path" class="flex">
                                        <a :href="act.doc_jugement_path" target="_blank" class="px-4 py-2 bg-white border border-gray-200 rounded-xl text-xs font-black text-[#1E690F] uppercase tracking-widest shadow-sm hover:bg-gray-50 transition-all flex items-center gap-1.5">
                                            <DocumentIcon class="w-4 h-4 text-[#1E690F]" />
                                            Copie du Jugement
                                        </a>
                                    </div>
                                </div>

                                <!-- Parents des Époux -->
                                <div class="pt-6 border-t border-gray-100 space-y-6">
                                    <h4 class="text-xs font-black text-gray-400 uppercase tracking-widest">Parents des Époux</h4>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                        <!-- Parents Époux -->
                                        <div class="space-y-4 p-5 bg-blue-50/10 rounded-2xl border border-blue-100/30">
                                            <h5 class="text-[10px] font-black text-blue-900 uppercase tracking-widest border-b border-blue-100/50 pb-1">De l'Époux</h5>
                                            <!-- Père -->
                                            <div class="text-xs">
                                                <span class="text-gray-400 uppercase font-bold text-[9px] block">Père</span>
                                                <span class="text-gray-800 font-black block">{{ act.spouses_metadata?.husband_father_first_name }} {{ act.spouses_metadata?.husband_father_last_name }}</span>
                                                <span class="text-gray-500 block">Né le : {{ formatDate(act.spouses_metadata?.husband_father_date_of_birth) || 'Non renseigné' }} | Profession : {{ act.spouses_metadata?.husband_father_profession || 'Non renseigné' }}</span>
                                                <span class="text-gray-500 block italic">Domicile : {{ act.spouses_metadata?.husband_father_domicile || 'Non renseigné' }}</span>
                                            </div>
                                            <!-- Mère -->
                                            <div class="text-xs pt-2 border-t border-blue-100/30">
                                                <span class="text-gray-400 uppercase font-bold text-[9px] block">Mère</span>
                                                <span class="text-gray-800 font-black block">{{ act.spouses_metadata?.husband_mother_first_name }} {{ act.spouses_metadata?.husband_mother_last_name }}</span>
                                                <span class="text-gray-500 block">Née le : {{ formatDate(act.spouses_metadata?.husband_mother_date_of_birth) || 'Non renseigné' }} | Profession : {{ act.spouses_metadata?.husband_mother_profession || 'Non renseigné' }}</span>
                                                <span class="text-gray-500 block italic">Domicile : {{ act.spouses_metadata?.husband_mother_domicile || 'Non renseigné' }}</span>
                                            </div>
                                        </div>

                                        <!-- Parents Épouse -->
                                        <div class="space-y-4 p-5 bg-pink-50/10 rounded-2xl border border-pink-100/30">
                                            <h5 class="text-[10px] font-black text-pink-900 uppercase tracking-widest border-b border-pink-100/50 pb-1">De l'Épouse</h5>
                                            <!-- Père -->
                                            <div class="text-xs">
                                                <span class="text-gray-400 uppercase font-bold text-[9px] block">Père</span>
                                                <span class="text-gray-800 font-black block">{{ act.spouses_metadata?.wife_father_first_name }} {{ act.spouses_metadata?.wife_father_last_name }}</span>
                                                <span class="text-gray-500 block">Né le : {{ formatDate(act.spouses_metadata?.wife_father_date_of_birth) || 'Non renseigné' }} | Profession : {{ act.spouses_metadata?.wife_father_profession || 'Non renseigné' }}</span>
                                                <span class="text-gray-500 block italic">Domicile : {{ act.spouses_metadata?.wife_father_domicile || 'Non renseigné' }}</span>
                                            </div>
                                            <!-- Mère -->
                                            <div class="text-xs pt-2 border-t border-pink-100/30">
                                                <span class="text-gray-400 uppercase font-bold text-[9px] block">Mère</span>
                                                <span class="text-gray-800 font-black block">{{ act.spouses_metadata?.wife_mother_first_name }} {{ act.spouses_metadata?.wife_mother_last_name }}</span>
                                                <span class="text-gray-500 block">Née le : {{ formatDate(act.spouses_metadata?.wife_mother_date_of_birth) || 'Non renseigné' }} | Profession : {{ act.spouses_metadata?.wife_mother_profession || 'Non renseigné' }}</span>
                                                <span class="text-gray-500 block italic">Domicile : {{ act.spouses_metadata?.wife_mother_domicile || 'Non renseigné' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Deces Context -->
                            <div v-if="type === 'deces'" class="space-y-8">
                                <!-- Acte & Défunt Details -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                    <!-- Informations de l'Acte de Décès -->
                                    <div class="p-6 bg-gray-50/50 rounded-2xl border border-gray-150 space-y-4">
                                        <div class="flex items-center gap-2 border-b border-gray-250 pb-2">
                                            <div class="w-2 h-2 bg-[#1E690F] rounded-full"></div>
                                            <h4 class="text-xs font-black text-gray-900 uppercase tracking-widest">Acte de Décès</h4>
                                        </div>
                                        <div class="space-y-3 text-xs">
                                            <div>
                                                <span class="text-gray-400 font-bold block uppercase tracking-wider text-[9px]">Date du Décès</span>
                                                <span class="text-gray-900 font-black text-sm">{{ formatDate(act.date_of_death) }}<span v-if="act.time_of_death" class="text-gray-500 font-medium ml-2">à {{ act.time_of_death }}</span></span>
                                            </div>
                                            <div>
                                                <span class="text-gray-400 font-bold block uppercase tracking-wider text-[9px]">Lieu du Décès</span>
                                                <span class="text-gray-800 font-bold">{{ act.place_of_death }}</span>
                                            </div>
                                            <div v-if="act.health_facility">
                                                <span class="text-gray-400 font-bold block uppercase tracking-wider text-[9px]">Formation Sanitaire</span>
                                                <span class="text-green-700 font-bold">{{ act.health_facility }}</span>
                                            </div>
                                            <div v-if="act.act_registration_date">
                                                <span class="text-gray-400 font-bold block uppercase tracking-wider text-[9px]">Date d'Inscription</span>
                                                <span class="text-gray-800 font-bold">Fait à Enampore, le {{ formatDate(act.act_registration_date) }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Informations du Défunt -->
                                    <div class="p-6 bg-red-50/10 rounded-2xl border border-red-100/30 space-y-4">
                                        <div class="flex items-center gap-2 border-b border-red-100/50 pb-2">
                                            <div class="w-2 h-2 bg-red-600 rounded-full"></div>
                                            <h4 class="text-xs font-black text-red-900 uppercase tracking-widest">Le Défunt</h4>
                                        </div>
                                        <div class="space-y-3 text-xs">
                                            <div>
                                                <span class="text-gray-400 font-bold block uppercase tracking-wider text-[9px]">Identité</span>
                                                <span class="text-gray-900 font-black text-base block">{{ act.deceased_first_name }} {{ act.deceased_last_name }}</span>
                                                <span class="text-xs font-bold text-red-600">{{ act.gender === 'M' ? 'Masculin' : 'Féminin' }}</span>
                                            </div>
                                            <div class="grid grid-cols-2 gap-4">
                                                <div>
                                                    <span class="text-gray-400 font-bold block uppercase tracking-wider text-[9px]">Né(e) le</span>
                                                    <span class="text-gray-850 font-bold">{{ formatDate(act.date_of_birth) }}<span v-if="act.death_metadata?.time_of_birth" class="text-gray-500 font-medium ml-1">à {{ act.death_metadata.time_of_birth }}</span></span>
                                                </div>
                                                <div>
                                                    <span class="text-gray-400 font-bold block uppercase tracking-wider text-[9px]">À</span>
                                                    <span class="text-gray-850 font-bold italic">{{ act.death_metadata?.place_of_birth || '-' }}</span>
                                                </div>
                                            </div>
                                            <div class="grid grid-cols-2 gap-4">
                                                <div>
                                                    <span class="text-gray-400 font-bold block uppercase tracking-wider text-[9px]">Profession</span>
                                                    <span class="text-gray-850 font-bold">{{ act.death_metadata?.profession || '-' }}</span>
                                                </div>
                                                <div>
                                                    <span class="text-gray-400 font-bold block uppercase tracking-wider text-[9px]">Domicile</span>
                                                    <span class="text-gray-850 font-bold">{{ act.death_metadata?.domicile || '-' }}</span>
                                                </div>
                                            </div>
                                            <div class="grid grid-cols-2 gap-4 pt-1 border-t border-red-100/50">
                                                <div>
                                                    <span class="text-gray-400 font-bold block uppercase tracking-wider text-[9px]">Situation Matrimoniale</span>
                                                    <span class="text-gray-850 font-bold">{{ act.death_metadata?.marital_status || '-' }}</span>
                                                </div>
                                                <div>
                                                    <span class="text-gray-400 font-bold block uppercase tracking-wider text-[9px]">Précédemment marié à</span>
                                                    <span class="text-gray-850 font-bold">{{ act.death_metadata?.previously_married_to || '-' }}</span>
                                                </div>
                                            </div>
                                            <div v-if="act.cause_of_death">
                                                <span class="text-gray-400 font-bold block uppercase tracking-wider text-[9px]">Cause du Décès (Optionnel)</span>
                                                <span class="text-gray-850 font-medium italic">"{{ act.cause_of_death }}"</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Jugement d'autorisation -->
                                <div v-if="act.is_judgment || act.judgment_number" class="p-5 bg-gray-50 rounded-2xl border border-gray-200 flex flex-col md:flex-row md:items-center justify-between gap-4">
                                    <div class="space-y-1">
                                        <div class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Déclaration sur Jugement</div>
                                        <div class="text-sm font-black text-gray-900">Jugement d'autorisation N° {{ act.judgment_number }}</div>
                                        <div class="text-xs text-gray-500 font-bold">Rendu le {{ formatDate(act.judgment_date) }} par la juridiction : {{ act.judgment_court }}</div>
                                    </div>
                                    <div v-if="act.doc_jugement_path" class="flex">
                                        <a :href="act.doc_jugement_path" target="_blank" class="px-4 py-2 bg-white border border-gray-200 rounded-xl text-xs font-black text-[#1E690F] uppercase tracking-widest shadow-sm hover:bg-gray-50 transition-all flex items-center gap-1.5">
                                            <DocumentIcon class="w-4 h-4 text-[#1E690F]" />
                                            Copie du Jugement
                                        </a>
                                    </div>
                                </div>

                                <!-- Parents du Défunt -->
                                <div class="pt-6 border-t border-gray-100 space-y-6">
                                    <h4 class="text-xs font-black text-gray-400 uppercase tracking-widest">Parents du Défunt</h4>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                        <!-- Père -->
                                        <div class="p-5 bg-blue-50/10 rounded-2xl border border-blue-100/30 text-xs space-y-2">
                                            <span class="text-gray-400 uppercase font-bold text-[9px] block">Père du Défunt</span>
                                            <span class="text-gray-800 font-black block text-sm">{{ act.death_metadata?.father_first_name }} {{ act.death_metadata?.father_last_name }}</span>
                                            <div v-if="act.death_metadata?.father_date_of_birth" class="text-gray-500 font-medium">Né le : {{ formatDate(act.death_metadata.father_date_of_birth) }}</div>
                                            <div v-if="act.death_metadata?.father_profession" class="text-gray-500 font-medium">Profession : {{ act.death_metadata.father_profession }}</div>
                                            <div v-if="act.death_metadata?.father_domicile" class="text-gray-500 font-medium italic">Domicile : {{ act.death_metadata.father_domicile }}</div>
                                        </div>
                                        <!-- Mère -->
                                        <div class="p-5 bg-pink-50/10 rounded-2xl border border-pink-100/30 text-xs space-y-2">
                                            <span class="text-gray-400 uppercase font-bold text-[9px] block">Mère du Défunt</span>
                                            <span class="text-gray-800 font-black block text-sm">{{ act.death_metadata?.mother_first_name }} {{ act.death_metadata?.mother_last_name }}</span>
                                            <div v-if="act.death_metadata?.mother_date_of_birth" class="text-gray-500 font-medium">Née le : {{ formatDate(act.death_metadata.mother_date_of_birth) }}</div>
                                            <div v-if="act.death_metadata?.mother_profession" class="text-gray-500 font-medium">Profession : {{ act.death_metadata.mother_profession }}</div>
                                            <div v-if="act.death_metadata?.mother_domicile" class="text-gray-500 font-medium italic">Domicile : {{ act.death_metadata.mother_domicile }}</div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Déclarant -->
                                <div v-if="act.death_metadata?.declarant_first_name || act.death_metadata?.declarant_last_name" class="pt-6 border-t border-gray-100">
                                    <h4 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-3">Déclarant du Décès</h4>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs p-5 bg-gray-50/50 rounded-2xl border border-gray-100">
                                        <div><span class="text-gray-400">Nom complet :</span> <span class="font-bold text-gray-900">{{ act.death_metadata.declarant_first_name }} {{ act.death_metadata.declarant_last_name }}</span></div>
                                        <div v-if="act.death_metadata.declarant_relationship"><span class="text-gray-400">Degré de parenté / Relation :</span> <span class="font-bold text-gray-900">{{ act.death_metadata.declarant_relationship }}</span></div>
                                        <div v-if="act.death_metadata.declarant_profession"><span class="text-gray-400">Profession :</span> <span class="font-bold text-gray-900">{{ act.death_metadata.declarant_profession }}</span></div>
                                        <div v-if="act.death_metadata.declarant_address"><span class="text-gray-400">Adresse :</span> <span class="font-bold text-gray-900">{{ act.death_metadata.declarant_address }}</span></div>
                                        <div v-if="act.death_metadata.declarant_id_number"><span class="text-gray-400">N° Identification (CNI) :</span> <span class="font-bold text-gray-900">{{ act.death_metadata.declarant_id_number }}</span></div>
                                        <div v-if="act.death_metadata.declarant_date_time"><span class="text-gray-400">Déclaré le :</span> <span class="font-bold text-gray-900">{{ formatDate(act.death_metadata.declarant_date_time) }}</span></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Versions / Historic -->
                    <div v-if="versions && versions.length > 1" class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8">
                        <h3 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-6 flex items-center gap-2">
                            <ClockIcon class="h-4 w-4" />
                            Historique des Rectifications
                        </h3>
                        <div class="space-y-4">
                            <div v-for="version in versions" :key="version.id" 
                                 class="flex items-center justify-between p-4 rounded-2xl border border-gray-50 hover:bg-gray-50 transition-colors">
                                <div class="flex items-center gap-4">
                                    <div class="h-8 w-8 bg-blue-50 rounded-lg flex items-center justify-center text-[10px] font-black text-blue-600">V{{ version.version_number }}</div>
                                    <div>
                                        <div class="text-xs font-black text-gray-900">{{ formatDate(version.created_at) }}</div>
                                        <div v-if="version.rectification_reason" class="text-[10px] font-bold text-gray-400">{{ version.rectification_reason }}</div>
                                    </div>
                                </div>
                                <Link :href="`/acts/${type}/${version.id}`" class="text-[10px] font-black text-blue-600 uppercase">Consulter</Link>
                            </div>
                        </div>
                    </div>

                    <!-- Additional Information / Comments -->
                    <div v-if="act.officer_comments || act.parents_metadata || act.witnesses_metadata" class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8">
                         <h3 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-6 flex items-center gap-2">
                            <PlusCircleIcon class="h-4 w-4" />
                            Informations Complémentaires
                        </h3>
                        
                        <div class="space-y-6">
                            <div v-if="act.officer_comments" class="p-4 bg-gray-50 rounded-2xl border border-gray-100">
                                <h4 class="text-[9px] font-black text-gray-400 uppercase mb-2">{{ ['naissance', 'mariage', 'deces'].includes(type) ? 'Mentions marginales' : "Observations de l'officier" }}</h4>
                                <p class="text-sm text-gray-700 italic">"{{ act.officer_comments }}"</p>
                            </div>

                            <div v-if="type === 'naissance' && act.parents_metadata" class="space-y-4">
                                <div v-if="act.act_registration_date" class="p-4 bg-green-50/50 rounded-2xl border border-green-100">
                                    <h4 class="text-[9px] font-black text-gray-400 uppercase mb-1">Date d'Inscription</h4>
                                    <p class="text-sm font-black text-gray-900">Fait à Enampore, le {{ formatDate(act.act_registration_date) }}</p>
                                </div>
                                <!-- Pièces justificatives -->
                                <div v-if="act.doc_cni_pere_path || act.doc_cni_mere_path || act.doc_acte_naissance_path || act.doc_cni_declarant_path || act.doc_autres_path || act.doc_jugement_path" class="p-4 bg-gray-50 rounded-2xl border border-gray-100">
                                    <h4 class="text-[9px] font-black text-gray-400 uppercase mb-3">Pièces Justificatives</h4>
                                    <div class="space-y-2">
                                        <a v-if="act.doc_cni_pere_path" :href="act.doc_cni_pere_path" target="_blank" class="flex items-center gap-2 text-xs font-bold text-[#1E690F] hover:underline"><span class="text-gray-500">CNI Père :</span> Visualiser le PDF</a>
                                        <a v-if="act.doc_cni_mere_path" :href="act.doc_cni_mere_path" target="_blank" class="flex items-center gap-2 text-xs font-bold text-[#1E690F] hover:underline"><span class="text-gray-500">CNI Mère :</span> Visualiser le PDF</a>
                                        <a v-if="act.doc_acte_naissance_path" :href="act.doc_acte_naissance_path" target="_blank" class="flex items-center gap-2 text-xs font-bold text-[#1E690F] hover:underline"><span class="text-gray-500">Acte/Attestation :</span> Visualiser le PDF</a>
                                        <a v-if="act.doc_cni_declarant_path" :href="act.doc_cni_declarant_path" target="_blank" class="flex items-center gap-2 text-xs font-bold text-[#1E690F] hover:underline"><span class="text-gray-500">CNI Déclarant :</span> Visualiser le PDF</a>
                                        <a v-if="act.doc_jugement_path" :href="act.doc_jugement_path" target="_blank" class="flex items-center gap-2 text-xs font-bold text-[#1E690F] hover:underline"><span class="text-gray-500">Copie du Jugement :</span> Visualiser le PDF</a>
                                        <a v-if="act.doc_autres_path" :href="act.doc_autres_path" target="_blank" class="flex items-center gap-2 text-xs font-bold text-[#1E690F] hover:underline"><span class="text-gray-500">Autres pièces :</span> Visualiser le PDF</a>
                                    </div>
                                </div>
                            </div>

                            <!-- Témoins du Mariage / Décès -->
                            <div v-if="['mariage', 'deces'].includes(type) && act.witnesses_metadata && act.witnesses_metadata.length > 0" class="space-y-4">
                                <h4 class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">{{ type === 'mariage' ? 'Témoins du Mariage' : 'Témoins du Décès' }}</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div v-for="(witness, idx) in act.witnesses_metadata" :key="idx" class="p-4 bg-gray-50/50 rounded-2xl border border-gray-100 space-y-2">
                                        <div class="flex items-center justify-between border-b border-gray-100 pb-1">
                                            <h5 class="text-[9px] font-black text-[#1E690F] uppercase tracking-wider">Témoin {{ idx + 1 }}</h5>
                                            <a v-if="witness.doc_cni_path" :href="witness.doc_cni_path" target="_blank" class="text-[9px] font-black text-[#1E690F] hover:underline flex items-center gap-1">
                                                <DocumentIcon class="w-3 h-3" />
                                                Voir CNI (PDF)
                                            </a>
                                        </div>
                                        <p class="text-sm font-black text-gray-900">{{ witness.first_name }} {{ witness.last_name }}</p>
                                        <div class="grid grid-cols-2 gap-2 text-xs text-gray-500 font-medium">
                                            <div>CNI : {{ witness.id_number || 'Non renseigné' }}</div>
                                            <div>Profession : {{ witness.profession || 'Non renseignée' }}</div>
                                        </div>
                                        <div class="text-xs text-gray-500 italic">Adresse : {{ witness.address || 'Non renseignée' }}</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Pièces Justificatives Mariage (9 catégories) -->
                            <div v-if="type === 'mariage' && (act.doc_cni_husband_path || act.doc_cni_wife_path || act.doc_birth_husband_path || act.doc_birth_wife_path || act.doc_domicile_path || act.doc_medical_path || act.doc_parental_auth_path || act.doc_jugement_path || act.doc_autres_path)" class="p-4 bg-gray-50 rounded-2xl border border-gray-100">
                                <h4 class="text-[9px] font-black text-gray-400 uppercase mb-3">Pièces Justificatives</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                    <a v-if="act.doc_cni_husband_path" :href="act.doc_cni_husband_path" target="_blank" class="flex items-center gap-2 text-xs font-bold text-[#1E690F] hover:underline">
                                        <DocumentIcon class="w-3.5 h-3.5 text-[#1E690F]" />
                                        <span class="text-gray-500">CNI Époux :</span> Visualiser
                                    </a>
                                    <a v-if="act.doc_cni_wife_path" :href="act.doc_cni_wife_path" target="_blank" class="flex items-center gap-2 text-xs font-bold text-[#1E690F] hover:underline">
                                        <DocumentIcon class="w-3.5 h-3.5 text-[#1E690F]" />
                                        <span class="text-gray-500">CNI Épouse :</span> Visualiser
                                    </a>
                                    <a v-if="act.doc_birth_husband_path" :href="act.doc_birth_husband_path" target="_blank" class="flex items-center gap-2 text-xs font-bold text-[#1E690F] hover:underline">
                                        <DocumentIcon class="w-3.5 h-3.5 text-[#1E690F]" />
                                        <span class="text-gray-500">Acte Naissance Époux :</span> Visualiser
                                    </a>
                                    <a v-if="act.doc_birth_wife_path" :href="act.doc_birth_wife_path" target="_blank" class="flex items-center gap-2 text-xs font-bold text-[#1E690F] hover:underline">
                                        <DocumentIcon class="w-3.5 h-3.5 text-[#1E690F]" />
                                        <span class="text-gray-500">Acte Naissance Épouse :</span> Visualiser
                                    </a>
                                    <a v-if="act.doc_domicile_path" :href="act.doc_domicile_path" target="_blank" class="flex items-center gap-2 text-xs font-bold text-[#1E690F] hover:underline">
                                        <DocumentIcon class="w-3.5 h-3.5 text-[#1E690F]" />
                                        <span class="text-gray-500">Certif. Domicile :</span> Visualiser
                                    </a>
                                    <a v-if="act.doc_medical_path" :href="act.doc_medical_path" target="_blank" class="flex items-center gap-2 text-xs font-bold text-[#1E690F] hover:underline">
                                        <DocumentIcon class="w-3.5 h-3.5 text-[#1E690F]" />
                                        <span class="text-gray-500">Certif. Médical :</span> Visualiser
                                    </a>
                                    <a v-if="act.doc_parental_auth_path" :href="act.doc_parental_auth_path" target="_blank" class="flex items-center gap-2 text-xs font-bold text-[#1E690F] hover:underline">
                                        <DocumentIcon class="w-3.5 h-3.5 text-[#1E690F]" />
                                        <span class="text-gray-500">Autorisation Parentale :</span> Visualiser
                                    </a>
                                    <a v-if="act.doc_jugement_path" :href="act.doc_jugement_path" target="_blank" class="flex items-center gap-2 text-xs font-bold text-[#1E690F] hover:underline">
                                        <DocumentIcon class="w-3.5 h-3.5 text-[#1E690F]" />
                                        <span class="text-gray-500">Jugement :</span> Visualiser
                                    </a>
                                    <a v-if="act.doc_autres_path" :href="act.doc_autres_path" target="_blank" class="flex items-center gap-2 text-xs font-bold text-[#1E690F] hover:underline col-span-2">
                                        <DocumentIcon class="w-3.5 h-3.5 text-[#1E690F]" />
                                        <span class="text-gray-500">Autres pièces :</span> Visualiser
                                    </a>
                                </div>
                            </div>

                            <!-- Pièces Justificatives Décès (5 catégories) -->
                            <div v-if="type === 'deces' && (act.doc_death_cert_path || act.doc_deceased_id_path || act.doc_declarant_id_path || act.doc_jugement_path || act.doc_autres_path)" class="p-4 bg-gray-50 rounded-2xl border border-gray-100">
                                <h4 class="text-[9px] font-black text-gray-400 uppercase mb-3">Pièces Justificatives</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                    <a v-if="act.doc_death_cert_path" :href="act.doc_death_cert_path" target="_blank" class="flex items-center gap-2 text-xs font-bold text-[#1E690F] hover:underline">
                                        <DocumentIcon class="w-3.5 h-3.5 text-[#1E690F]" />
                                        <span class="text-gray-500">Certificat de Décès :</span> Visualiser
                                    </a>
                                    <a v-if="act.doc_deceased_id_path" :href="act.doc_deceased_id_path" target="_blank" class="flex items-center gap-2 text-xs font-bold text-[#1E690F] hover:underline">
                                        <DocumentIcon class="w-3.5 h-3.5 text-[#1E690F]" />
                                        <span class="text-gray-500">Identité du Défunt :</span> Visualiser
                                    </a>
                                    <a v-if="act.doc_declarant_id_path" :href="act.doc_declarant_id_path" target="_blank" class="flex items-center gap-2 text-xs font-bold text-[#1E690F] hover:underline">
                                        <DocumentIcon class="w-3.5 h-3.5 text-[#1E690F]" />
                                        <span class="text-gray-500">CNI du Déclarant :</span> Visualiser
                                    </a>
                                    <a v-if="act.doc_jugement_path" :href="act.doc_jugement_path" target="_blank" class="flex items-center gap-2 text-xs font-bold text-[#1E690F] hover:underline">
                                        <DocumentIcon class="w-3.5 h-3.5 text-[#1E690F]" />
                                        <span class="text-gray-500">Copie du Jugement :</span> Visualiser
                                    </a>
                                    <a v-if="act.doc_autres_path" :href="act.doc_autres_path" target="_blank" class="flex items-center gap-2 text-xs font-bold text-[#1E690F] hover:underline col-span-2">
                                        <DocumentIcon class="w-3.5 h-3.5 text-[#1E690F]" />
                                        <span class="text-gray-500">Autres pièces :</span> Visualiser
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Meta & Actions -->
                <div class="space-y-8">
                    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8">
                        <h3 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-6 flex items-center gap-2">
                            <CheckBadgeIcon class="h-4 w-4" />
                            Statut de l'acte
                        </h3>
                        <div class="flex items-center gap-3">
                            <span class="px-4 py-2 bg-blue-50 text-blue-700 rounded-2xl text-xs font-black uppercase tracking-widest">
                                {{ act.status }}
                            </span>
                        </div>
                        
                        <div class="mt-8 space-y-4 pt-8 border-t border-gray-50">
                            <div class="flex justify-between items-center text-[10px] font-bold">
                                <span class="text-gray-400 uppercase">Enregistré le</span>
                                <span class="text-gray-900">{{ formatDate(act.created_at) }}</span>
                            </div>
                            <div class="flex justify-between items-center text-[10px] font-bold">
                                <span class="text-gray-400 uppercase">Centre</span>
                                <span class="text-gray-900">{{ act.registry?.name || 'CENTRE PRINCIPAL' }}</span>
                            </div>
                        </div>
                    </div>
                    
                    <div v-if="['brouillon', 'a_corriger'].includes(act.status)" class="space-y-4">
                        <Link :href="`/acts/${type}/${act.id}/edit`" class="w-full py-4 bg-white border-2 border-dashed border-gray-200 rounded-3xl text-gray-400 hover:border-blue-400 hover:text-blue-600 transition-all font-black text-xs uppercase flex items-center justify-center gap-2 group">
                             <PencilSquareIcon class="h-4 w-4 group-hover:scale-110 transition-transform" />
                             Modifier l'acte
                        </Link>
                    </div>

                    <!-- CONTROL PANEL FOR VALIDATION -->
                    <div v-if="act.status !== 'signe'" class="space-y-3 mt-4">
                        <!-- Officier Actions -->
                        <template v-if="hasRole(`Officier d'état-civil`) || hasRole('Administrateur technique')">
                            <button v-if="['brouillon', 'a_corriger'].includes(act.status)" @click="updateStatus('valide')" class="w-full py-4 bg-green-50 text-green-600 hover:bg-green-100 rounded-3xl transition-all font-black text-xs uppercase flex items-center justify-center gap-2">
                                Valider et Approuver
                            </button>
                            <button v-if="['brouillon', 'valide'].includes(act.status)" @click="updateStatus('a_corriger')" class="w-full py-4 bg-yellow-50 text-yellow-600 hover:bg-yellow-100 rounded-3xl transition-all font-black text-xs uppercase flex items-center justify-center gap-2">
                                Renvoyer à la correction
                            </button>
                            <button v-if="['brouillon', 'valide', 'a_corriger'].includes(act.status)" @click="updateStatus('rejete')" class="w-full py-4 bg-red-50 text-red-600 hover:bg-red-100 rounded-3xl transition-all font-black text-xs uppercase flex items-center justify-center gap-2">
                                Rejeter Définitivement
                            </button>
                        </template>

                        <!-- Maire Actions -->
                        <template v-if="hasRole('Maire ou Délégué') || hasRole('Administrateur technique')">
                            <button v-if="act.status === 'valide'" @click="updateStatus('signe')" class="w-full py-4 bg-blue-600 text-white shadow-xl shadow-blue-600/20 hover:scale-[1.02] active:scale-95 rounded-3xl transition-all font-black text-xs uppercase tracking-widest flex items-center justify-center gap-2">
                                Signer et Sceller
                            </button>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
