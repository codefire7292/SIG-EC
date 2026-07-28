<script setup>
import { computed, ref } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
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
    DocumentIcon,
    CheckCircleIcon,
    ArrowPathIcon,
    ExclamationTriangleIcon,
    ArrowDownTrayIcon,
    ChevronRightIcon,
    UserIcon,
    ShieldCheckIcon,
    SparklesIcon,
    BuildingOfficeIcon,
    DocumentTextIcon
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

const typePlural = computed(() => {
    switch (props.type) {
        case 'naissance': return 'Naissances';
        case 'mariage': return 'Mariages';
        case 'deces': return 'Décès';
        default: return 'Actes';
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

const formatTime = (timeStr) => {
    if (!timeStr) return '';
    const parts = timeStr.split(':');
    return parts.length >= 2 ? `${parts[0]}h${parts[1]}` : timeStr;
};

// Pour les actes de naissance : si aucun déclarant tiers n'est renseigné,
// le père est considéré comme déclarant par défaut (règle légale).
const effectiveDeclarant = computed(() => {
    if (props.type !== 'naissance') return null;
    const meta = props.act?.parents_metadata;
    const hasThirdPartyDeclarant = !!(meta?.declarant_first_name || meta?.declarant_last_name);
    if (hasThirdPartyDeclarant) {
        return {
            name: `${meta.declarant_first_name || ''} ${meta.declarant_last_name || ''}`.trim(),
            profession: meta.declarant_profession || null,
            address: meta.declarant_address || null,
            id_number: meta.declarant_id_number || null,
            date: meta.declarant_date || null,
            judgment_ref: meta.declarant_judgment_ref || null,
            isDefault: false,
        };
    }
    // Fallback : le père est le déclarant par défaut
    const fatherName = props.act?.father_name;
    if (!fatherName) return null;
    return {
        name: fatherName,
        profession: meta?.father_profession || null,
        address: meta?.father_domicile || null,
        id_number: null,
        date: null,
        judgment_ref: null,
        isDefault: true,
    };
});

const authUser = usePage().props.auth.user;

const hasRole = (role) => {
    if (!authUser) return false;
    if (authUser.role === role) return true;
    if (authUser.roles && Array.isArray(authUser.roles) && authUser.roles.map(r => r.name).includes(role)) return true;
    return false;
};

const showStatusModal = ref(false);
const pendingStatus = ref('');
const statusModalTitle = ref('');
const statusModalDescription = ref('');
const statusModalConfirmClass = ref('');
const statusModalConfirmText = ref('');

const openStatusModal = (newStatus) => {
    pendingStatus.value = newStatus;
    
    if (newStatus === 'valide') {
        statusModalTitle.value = 'Valider et Approuver';
        statusModalDescription.value = "Confirmez-vous le passage au statut: VALIDÉ ? L'acte sera approuvé et disponible pour la signature finale du Maire.";
        statusModalConfirmText.value = 'Valider et Approuver';
        statusModalConfirmClass.value = 'bg-green-600 hover:bg-green-700 text-white focus:ring-green-500';
    } else if (newStatus === 'a_corriger') {
        statusModalTitle.value = 'Renvoyer à la correction';
        statusModalDescription.value = "Confirmez-vous le passage au statut: À CORRIGER ? L'acte sera renvoyé à l'agent d'état civil pour modification.";
        statusModalConfirmText.value = 'Renvoyer pour correction';
        statusModalConfirmClass.value = 'bg-amber-500 hover:bg-amber-600 text-white focus:ring-amber-500';
    } else if (newStatus === 'rejete') {
        statusModalTitle.value = 'Rejeter Définitivement';
        statusModalDescription.value = "Attention : Confirmez-vous le rejet définitif de cet acte ? Cette action est irréversible.";
        statusModalConfirmText.value = 'Rejeter Définitivement';
        statusModalConfirmClass.value = 'bg-red-600 hover:bg-red-700 text-white focus:ring-red-500';
    } else if (newStatus === 'signe') {
        statusModalTitle.value = 'Signer et Sceller';
        statusModalDescription.value = "Voulez-vous procéder à la SIGNATURE FINALE ? Après cette action, l'acte deviendra IMMUABLE et sera définitivement archivé au registre numérique.";
        statusModalConfirmText.value = 'Signer et Sceller';
        statusModalConfirmClass.value = 'bg-emerald-600 hover:bg-emerald-700 text-white focus:ring-emerald-500';
    }
    
    showStatusModal.value = true;
};

const confirmStatusChange = () => {
    showStatusModal.value = false;
    router.post(`/acts/${props.type}/${props.act.id}/status`, {
        status: pendingStatus.value
    }, { preserveScroll: true });
};

const getStatusModalIcon = () => {
    if (pendingStatus.value === 'valide') return CheckCircleIcon;
    if (pendingStatus.value === 'a_corriger') return ArrowPathIcon;
    if (pendingStatus.value === 'rejete') return ExclamationTriangleIcon;
    if (pendingStatus.value === 'signe') return CheckBadgeIcon;
    return CheckCircleIcon;
};

const getStatusBadge = (status) => {
    switch (status) {
        case 'signe':
            return { label: 'Signé & Scellé', bg: 'bg-emerald-100/80 text-emerald-800 border-emerald-200' };
        case 'valide':
            return { label: 'Validé (En attente de signature)', bg: 'bg-blue-100/80 text-blue-800 border-blue-200' };
        case 'a_corriger':
            return { label: 'À Corriger', bg: 'bg-amber-100/80 text-amber-800 border-amber-200' };
        case 'brouillon':
            return { label: 'Brouillon', bg: 'bg-gray-100 text-gray-700 border-gray-200' };
        case 'rejete':
            return { label: 'Rejeté', bg: 'bg-red-100/80 text-red-800 border-red-200' };
        default:
            return { label: status, bg: 'bg-gray-100 text-gray-700 border-gray-200' };
    }
};

const goBack = () => {
    if (window.history.length > 1) {
        window.history.back();
    } else {
        router.visit(`/acts/${props.type}`);
    }
};
</script>

<template>
    <Head :title="`${title} - ${act.reference_number || 'Consultation'}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-2">
                <!-- Breadcrumbs -->
                <nav class="flex items-center text-xs font-bold text-gray-400 gap-2">
                    <Link href="/acts/hub" class="hover:text-[#1E690F] transition-colors">Registres</Link>
                    <ChevronRightIcon class="w-3 h-3 text-gray-300" />
                    <button type="button" @click="goBack" class="hover:text-[#1E690F] transition-colors cursor-pointer">{{ typePlural }}</button>
                    <ChevronRightIcon class="w-3 h-3 text-gray-300" />
                    <span class="text-gray-700 font-extrabold">{{ act.reference_number || 'Détails de l\'acte' }}</span>
                </nav>

                <!-- Action Header Bar -->
                <div class="flex items-center justify-between mt-1">
                    <div class="flex items-center gap-3">
                        <button type="button" @click="goBack" class="p-2.5 bg-white border border-gray-200 rounded-2xl shadow-sm hover:bg-gray-50 hover:border-gray-300 transition-all active:scale-95 cursor-pointer" title="Retour à la liste précédente">
                            <ArrowLeftIcon class="h-5 w-5 text-gray-600" />
                        </button>
                        <div>
                            <h2 class="font-black text-2xl text-gray-900 tracking-tight flex items-center gap-3">
                                {{ title }}
                                <span class="px-3 py-1 rounded-full text-xs font-black uppercase tracking-wider border shadow-xs" :class="getStatusBadge(act.status).bg">
                                    {{ getStatusBadge(act.status).label }}
                                </span>
                            </h2>
                            <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mt-0.5">
                                Référence officielle : <span class="text-gray-900 font-black">{{ act.reference_number || 'SANS RÉFÉRENCE' }}</span>
                            </p>
                        </div>
                    </div>

                    <!-- Quick Print / Edit Action -->
                    <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                        <Link v-if="['brouillon', 'a_corriger'].includes(act.status)" :href="`/acts/${type}/${act.id}/edit`" 
                              class="inline-flex items-center px-5 py-3 bg-white border border-gray-200 text-gray-700 rounded-2xl text-xs font-black uppercase tracking-wider shadow-sm hover:bg-gray-50 transition-all active:scale-95">
                            <PencilSquareIcon class="h-4 w-4 mr-2 text-gray-500" />
                            Modifier
                        </Link>
                        <template v-if="act.status === 'signe'">
                            <!-- Bouton 1 : Télécharger l'Extrait PDF (Original) -->
                            <a :href="`/verify/${type}/${act.uuid}/download`" target="_blank" 
                               title="Extrait d'acte d'état civil officiel"
                               class="inline-flex items-center px-5 py-3 bg-[#1E690F] hover:bg-[#185709] text-white rounded-2xl text-xs font-black uppercase tracking-wider shadow-lg shadow-green-900/10 transition-all active:scale-95">
                                <ArrowDownTrayIcon class="h-4 w-4 mr-2" />
                                Télécharger l'Extrait PDF
                            </a>
                            <!-- Groupe collé : Volet 1, Volet 2 & Volet 3 (Côte à côte) -->
                            <div class="inline-flex rounded-2xl shadow-lg overflow-hidden border border-[#185709] divide-x divide-white/20">
                                <a :href="`/verify/${type}/${act.uuid}/download?volet=1`" target="_blank" 
                                   title="Exemplaire conservé au Centre d'État Civil (Mairie)"
                                   class="inline-flex items-center px-4 py-3 bg-emerald-700 hover:bg-emerald-800 text-white text-xs font-black uppercase tracking-wider transition-all active:scale-95">
                                    <ArrowDownTrayIcon class="h-4 w-4 mr-1.5" />
                                    Volet 1 (Mairie)
                                </a>
                                <a :href="`/verify/${type}/${act.uuid}/download?volet=2`" target="_blank" 
                                   title="Exemplaire transmis au Greffe du Tribunal"
                                   class="inline-flex items-center px-4 py-3 bg-slate-800 hover:bg-slate-900 text-white text-xs font-black uppercase tracking-wider transition-all active:scale-95">
                                    <ArrowDownTrayIcon class="h-4 w-4 mr-1.5 text-amber-400" />
                                    Volet 2 (Tribunal)
                                </a>
                                <a :href="`/verify/${type}/${act.uuid}/download?volet=3`" target="_blank" 
                                   title="Exemplaire remis au Titulaire / Déclarant"
                                   class="inline-flex items-center px-4 py-3 bg-indigo-800 hover:bg-indigo-900 text-white text-xs font-black uppercase tracking-wider transition-all active:scale-95">
                                    <ArrowDownTrayIcon class="h-4 w-4 mr-1.5 text-indigo-300" />
                                    Volet 3 (Titulaire)
                                </a>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </template>

        <div class="max-w-5xl mx-auto space-y-8 pb-20 mt-6">
            <!-- Main Content Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <!-- Left Column: Primary Details -->
                <div class="lg:col-span-2 space-y-8">
                    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                        
                        <!-- Hero Card Banner -->
                        <div class="bg-gradient-to-r from-[#1E690F] via-[#267b14] to-[#154d0a] px-8 py-5 flex items-center justify-between text-white shadow-md">
                            <div class="flex items-center gap-3">
                                <div class="p-2.5 bg-white/10 backdrop-blur-md rounded-xl">
                                    <DocumentTextIcon class="h-6 w-6 text-white" />
                                </div>
                                <div>
                                    <span class="text-[10px] font-black uppercase tracking-widest text-green-200 block">Registre Officiel</span>
                                    <h3 class="text-lg font-black tracking-tight leading-tight">Détails Certifiés de l'Acte</h3>
                                </div>
                            </div>
                            <span class="px-3.5 py-1.5 bg-white/20 backdrop-blur-md text-white rounded-xl text-xs font-black uppercase tracking-wider border border-white/10">
                                Version {{ act.version_number }}
                            </span>
                        </div>
                        
                        <div class="p-8 space-y-8">
                            <!-- Naissance Context -->
                            <div v-if="type === 'naissance'" class="space-y-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 p-6 bg-gray-50/60 rounded-2xl border border-gray-100/80">
                                    <div>
                                        <h4 class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 flex items-center gap-1.5">
                                            <UserIcon class="h-3.5 w-3.5 text-[#1E690F]" />
                                            Enfant
                                        </h4>
                                        <div class="text-2xl font-black text-gray-900 tracking-tight">{{ act.first_name }} {{ act.last_name }}</div>
                                        <div class="inline-flex items-center mt-2 px-3 py-1 bg-blue-50 text-blue-700 rounded-lg text-xs font-extrabold border border-blue-100">
                                            {{ act.gender === 'M' ? 'Masculin' : 'Féminin' }}
                                        </div>
                                    </div>
                                    <div>
                                        <h4 class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 flex items-center gap-1.5">
                                            <CalendarIcon class="h-3.5 w-3.5 text-[#1E690F]" />
                                            Naissance
                                        </h4>
                                        <div class="text-base font-black text-gray-900">
                                            {{ formatDate(act.date_of_birth) }}
                                            <span v-if="act.time_of_birth" class="text-gray-500 font-bold text-xs ml-2">à {{ formatTime(act.time_of_birth) }}</span>
                                        </div>
                                        <div class="text-xs font-bold text-gray-500 italic mt-1 flex items-center gap-1">
                                            <MapPinIcon class="h-3.5 w-3.5 text-gray-400" />
                                            {{ act.place_of_birth }}
                                        </div>
                                        <div v-if="act.health_facility" class="text-xs font-extrabold text-[#1E690F] mt-2 bg-green-50 px-3 py-1 rounded-lg border border-green-100 inline-block">
                                            Formation : {{ act.health_facility }}
                                        </div>
                                    </div>
                                </div>

                                <!-- Jugement de Naissance -->
                                <div v-if="act.is_judgment" class="p-5 bg-emerald-50/50 rounded-2xl border border-emerald-100/80 space-y-3">
                                    <div class="flex items-center justify-between">
                                        <div class="px-3 py-1 bg-[#1E690F] text-white rounded-lg text-[10px] font-black uppercase tracking-wider shadow-xs">Jugement de Naissance</div>
                                        <span class="text-xs font-black text-emerald-800">Tribunal : {{ act.judgment_court }}</span>
                                    </div>
                                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-xs pt-1">
                                        <div>
                                            <div class="text-[9px] font-black text-gray-400 uppercase">N° Jugement</div>
                                            <div class="text-xs font-black text-gray-900">{{ act.judgment_number }}</div>
                                        </div>
                                        <div>
                                            <div class="text-[9px] font-black text-gray-400 uppercase">Date Jugement</div>
                                            <div class="text-xs font-black text-gray-900">{{ formatDate(act.judgment_date) }}</div>
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
                                </div>

                                <!-- Filiation (Père & Mère) -->
                                <div class="pt-4 border-t border-gray-100 grid grid-cols-1 md:grid-cols-2 gap-8">
                                    <div class="p-5 bg-blue-50/20 rounded-2xl border border-blue-100/40 space-y-2">
                                        <h4 class="text-[10px] font-black text-blue-900 uppercase tracking-widest">Père</h4>
                                        <div class="text-base font-black text-gray-900">{{ act.father_name || 'Non renseigné' }}</div>
                                        <div v-if="act.parents_metadata?.father_date_of_birth" class="text-xs text-gray-600 font-medium">Né le {{ formatDate(act.parents_metadata.father_date_of_birth) }}<span v-if="act.parents_metadata.father_place_of_birth"> — {{ act.parents_metadata.father_place_of_birth }}</span></div>
                                        <div v-if="act.parents_metadata?.father_domicile" class="text-xs text-gray-600 font-medium">Domicile : {{ act.parents_metadata.father_domicile }}</div>
                                        <div v-if="act.parents_metadata?.father_profession" class="text-xs text-gray-600 font-medium">Profession : {{ act.parents_metadata.father_profession }}</div>
                                    </div>

                                    <div class="p-5 bg-pink-50/20 rounded-2xl border border-pink-100/40 space-y-2">
                                        <h4 class="text-[10px] font-black text-pink-900 uppercase tracking-widest">Mère</h4>
                                        <div class="text-base font-black text-gray-900">{{ act.mother_name || 'Non renseignée' }}</div>
                                        <div v-if="act.parents_metadata?.mother_date_of_birth" class="text-xs text-gray-600 font-medium">Née le {{ formatDate(act.parents_metadata.mother_date_of_birth) }}<span v-if="act.parents_metadata.mother_place_of_birth"> — {{ act.parents_metadata.mother_place_of_birth }}</span></div>
                                        <div v-if="act.parents_metadata?.mother_domicile" class="text-xs text-gray-600 font-medium">Domicile : {{ act.parents_metadata.mother_domicile }}</div>
                                        <div v-if="act.parents_metadata?.mother_profession" class="text-xs text-gray-600 font-medium">Profession : {{ act.parents_metadata.mother_profession }}</div>
                                    </div>
                                </div>

                                <!-- Déclarant -->
                                <div v-if="effectiveDeclarant" class="p-5 bg-gray-50/70 rounded-2xl border border-gray-100 space-y-3">
                                    <div class="flex items-center justify-between">
                                        <h4 class="text-[10px] font-black text-gray-500 uppercase tracking-widest">Déclarant de la Naissance</h4>
                                        <span v-if="effectiveDeclarant.isDefault" class="px-2.5 py-1 bg-blue-50 text-blue-700 rounded-full text-[9px] font-black uppercase tracking-wider border border-blue-100">
                                            Père (par défaut)
                                        </span>
                                        <span v-else class="px-2.5 py-1 bg-purple-50 text-purple-700 rounded-full text-[9px] font-black uppercase tracking-wider border border-purple-100">
                                            Tiers déclarant
                                        </span>
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs font-medium">
                                        <div><span class="text-gray-400">Nom :</span> <span class="font-black text-gray-900">{{ effectiveDeclarant.name }}</span></div>
                                        <div v-if="effectiveDeclarant.profession"><span class="text-gray-400">Profession :</span> <span class="font-black text-gray-900">{{ effectiveDeclarant.profession }}</span></div>
                                        <div v-if="effectiveDeclarant.address"><span class="text-gray-400">Adresse :</span> <span class="font-black text-gray-900">{{ effectiveDeclarant.address }}</span></div>
                                        <div v-if="effectiveDeclarant.id_number"><span class="text-gray-400">ID / CNI :</span> <span class="font-black text-gray-900">{{ effectiveDeclarant.id_number }}</span></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Mariage Context -->
                            <div v-if="type === 'mariage'" class="space-y-8">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                    <!-- Époux (Mari) -->
                                    <div class="p-6 bg-blue-50/30 rounded-2xl border border-blue-100/50 space-y-4">
                                        <div class="flex items-center gap-2 border-b border-blue-100 pb-2">
                                            <div class="w-2 h-2 bg-blue-600 rounded-full"></div>
                                            <h4 class="text-xs font-black text-blue-900 uppercase tracking-widest">Époux (Mari)</h4>
                                        </div>
                                        <div class="space-y-2">
                                            <div class="text-xl font-black text-gray-900">{{ act.husband_first_name }} {{ act.husband_last_name }}</div>
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
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
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs pt-1">
                                                <div>
                                                    <span class="text-gray-400 font-bold block uppercase tracking-wider text-[9px]">Domicile</span>
                                                    <span class="text-gray-800 font-bold">{{ act.spouses_metadata?.husband_domicile || 'Non renseigné' }}</span>
                                                </div>
                                                <div>
                                                    <span class="text-gray-400 font-bold block uppercase tracking-wider text-[9px]">Résidence</span>
                                                    <span class="text-gray-800 font-bold">{{ act.spouses_metadata?.husband_residence || 'Non renseigné' }}</span>
                                                </div>
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
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
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
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs pt-1">
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
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-6 p-6 bg-gray-50 rounded-2xl border border-gray-100">
                                    <div>
                                        <h4 class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Célébration</h4>
                                        <div class="text-sm font-black text-gray-900">{{ formatDate(act.marriage_date) }}</div>
                                    </div>
                                    <div>
                                        <h4 class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Lieu</h4>
                                        <div class="text-sm font-bold text-gray-700 italic">{{ act.marriage_place }}</div>
                                    </div>
                                    <div>
                                        <h4 class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Option Matrimoniale</h4>
                                        <div class="text-sm font-black text-gray-900 uppercase">
                                            {{ act.marriage_option === 'monogamie' ? 'Monogamie' : (act.marriage_option === 'limitation_polygamie' ? 'Limitation de polygamie' : (act.marriage_option === 'polygamie' ? 'Polygamie' : act.marriage_option || 'Polygamie')) }}
                                        </div>
                                    </div>
                                    <div>
                                        <h4 class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Régime Matrimonial</h4>
                                        <div class="text-sm font-black text-[#1E690F] uppercase">
                                            {{ act.matrimonial_regime === 'separation_biens' ? 'Séparation des biens' : (act.matrimonial_regime === 'regime_dotal' ? 'Régime dotal' : (act.matrimonial_regime === 'participation_meubles_acquets' ? 'Participation aux acquêts' : act.matrimonial_regime || 'Séparation des biens')) }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Deces Context -->
                            <div v-if="type === 'deces'" class="space-y-8">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                    <div class="p-6 bg-gray-50/80 rounded-2xl border border-gray-200/70 space-y-3">
                                        <h4 class="text-xs font-black text-gray-900 uppercase tracking-widest border-b border-gray-200 pb-2">Décès</h4>
                                        <div class="space-y-2 text-xs">
                                            <div>
                                                <span class="text-gray-400 font-bold block uppercase text-[9px]">Date & Heure</span>
                                                <span class="text-gray-900 font-black text-sm">{{ formatDate(act.date_of_death) }}<span v-if="act.time_of_death" class="text-gray-500 font-bold ml-2">à {{ formatTime(act.time_of_death) }}</span></span>
                                            </div>
                                            <div>
                                                <span class="text-gray-400 font-bold block uppercase text-[9px]">Lieu du Décès</span>
                                                <span class="text-gray-800 font-bold">{{ act.place_of_death }}</span>
                                            </div>
                                            <div v-if="act.health_facility">
                                                <span class="text-gray-400 font-bold block uppercase text-[9px]">Formation Sanitaire</span>
                                                <span class="text-green-700 font-bold">{{ act.health_facility }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="p-6 bg-red-50/20 rounded-2xl border border-red-100/50 space-y-3">
                                        <h4 class="text-xs font-black text-red-900 uppercase tracking-widest border-b border-red-100/50 pb-2">Le Défunt</h4>
                                        <div class="space-y-2 text-xs">
                                            <div>
                                                <span class="text-gray-400 font-bold block uppercase text-[9px]">Identité</span>
                                                <span class="text-gray-900 font-black text-lg block">{{ act.deceased_first_name }} {{ act.deceased_last_name }}</span>
                                            </div>
                                            <div class="grid grid-cols-2 gap-4">
                                                <div>
                                                    <span class="text-gray-400 font-bold block uppercase text-[9px]">Né(e) le</span>
                                                    <span class="text-gray-800 font-bold">{{ formatDate(act.date_of_birth) }}</span>
                                                </div>
                                                <div>
                                                    <span class="text-gray-400 font-bold block uppercase text-[9px]">Sexe</span>
                                                    <span class="text-gray-800 font-bold">{{ act.gender === 'M' ? 'Masculin' : 'Féminin' }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Mentions Marginales & Observations -->
                            <div v-if="act.officer_comments" class="mt-8 p-6 bg-amber-50/70 rounded-3xl border border-amber-200/80 shadow-sm space-y-3">
                                <h4 class="text-xs font-black text-amber-900 uppercase tracking-widest flex items-center gap-2 border-b border-amber-200/60 pb-3">
                                    <DocumentTextIcon class="h-4 w-4 text-amber-700" />
                                    Mentions Marginales &amp; Observations
                                </h4>
                                <div class="text-xs font-bold text-amber-950 leading-relaxed bg-white/70 p-4 rounded-2xl border border-amber-200/40 whitespace-pre-line">
                                    {{ act.officer_comments }}
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                <!-- Right Column: Meta & Actions -->
                <div class="space-y-8">
                    <!-- Status Card -->
                    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8 space-y-6">
                        <h3 class="text-xs font-black text-gray-400 uppercase tracking-widest flex items-center gap-2">
                            <ShieldCheckIcon class="h-4 w-4 text-[#1E690F]" />
                            Statut Officiel
                        </h3>
                        
                        <div class="p-4 rounded-2xl border flex items-center justify-between" :class="getStatusBadge(act.status).bg">
                            <span class="font-black text-xs uppercase tracking-wider">
                                {{ getStatusBadge(act.status).label }}
                            </span>
                            <CheckCircleIcon class="w-5 h-5 stroke-[2.5]" />
                        </div>
                        
                        <div class="space-y-3 pt-4 border-t border-gray-100 text-xs">
                            <div class="flex justify-between items-center font-semibold">
                                <span class="text-gray-400">Date d'enregistrement</span>
                                <span class="text-gray-900 font-bold">{{ formatDate(act.created_at) }}</span>
                            </div>
                            <div class="flex justify-between items-center font-semibold">
                                <span class="text-gray-400">Centre d'État-Civil</span>
                                <span class="text-gray-900 font-bold">{{ act.registry?.name || 'CENTRE PRINCIPAL' }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Actions Panel -->
                    <div v-if="act.status !== 'signe'" class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6 space-y-3">
                        <h3 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-4">Actions Administratifs</h3>

                        <!-- Officier / Superviseur Actions -->
                        <template v-if="hasRole(`Officier d'état-civil`) || hasRole('Superviseur') || hasRole('Administrateur technique')">
                            <button v-if="['brouillon', 'a_corriger'].includes(act.status)" @click="openStatusModal('valide')" 
                                    class="w-full py-4 bg-green-50 text-green-700 hover:bg-green-100 rounded-2xl transition-all font-black text-xs uppercase tracking-wider flex items-center justify-center gap-2 cursor-pointer">
                                <CheckCircleIcon class="w-4 h-4" />
                                Valider et Approuver
                            </button>
                            <button v-if="['brouillon', 'valide'].includes(act.status)" @click="openStatusModal('a_corriger')" 
                                    class="w-full py-4 bg-amber-50 text-amber-700 hover:bg-amber-100 rounded-2xl transition-all font-black text-xs uppercase tracking-wider flex items-center justify-center gap-2 cursor-pointer">
                                <ArrowPathIcon class="w-4 h-4" />
                                Renvoyer à la correction
                            </button>
                            <button v-if="['brouillon', 'valide', 'a_corriger'].includes(act.status)" @click="openStatusModal('rejete')" 
                                    class="w-full py-4 bg-red-50 text-red-700 hover:bg-red-100 rounded-2xl transition-all font-black text-xs uppercase tracking-wider flex items-center justify-center gap-2 cursor-pointer">
                                <ExclamationTriangleIcon class="w-4 h-4" />
                                Rejeter Définitivement
                            </button>
                        </template>

                        <!-- Maire Actions -->
                        <template v-if="hasRole('Maire ou Délégué') || hasRole('Administrateur technique')">
                            <button v-if="act.status === 'valide'" @click="openStatusModal('signe')" 
                                    class="w-full py-4 bg-gradient-to-r from-emerald-600 to-green-700 text-white shadow-xl shadow-green-900/20 hover:scale-[1.02] active:scale-95 rounded-2xl transition-all font-black text-xs uppercase tracking-widest flex items-center justify-center gap-2 cursor-pointer">
                                <CheckBadgeIcon class="w-5 h-5 stroke-[2.5]" />
                                Signer et Sceller
                            </button>
                        </template>
                    </div>

                    <!-- PDF Download for Signed Acts -->
                    <div v-if="act.status === 'signe'" class="bg-gradient-to-br from-green-900 via-[#1E690F] to-slate-900 rounded-3xl p-6 text-white shadow-xl space-y-4">
                        <div class="flex items-center gap-3">
                            <SparklesIcon class="h-6 w-6 text-green-300" />
                            <div>
                                <h4 class="font-black text-sm uppercase tracking-wide">Acte Validé & Scellé</h4>
                                <p class="text-[10px] text-green-200 font-medium mt-0.5">Le document comporte le QR code et la signature électronique.</p>
                            </div>
                        </div>
                        <div class="space-y-3 pt-2">
                            <!-- Bouton 1 : Télécharger l'Extrait PDF (Main White Pill) -->
                            <a :href="`/verify/${type}/${act.uuid}/download`" target="_blank" 
                               class="w-full py-3.5 bg-white text-[#1E690F] hover:bg-green-50 active:scale-95 rounded-2xl transition-all font-black text-xs uppercase tracking-wider flex items-center justify-center gap-2 shadow-lg cursor-pointer">
                                <ArrowDownTrayIcon class="h-4 w-4 stroke-[2.5]" />
                                <span>Télécharger l'Extrait PDF</span>
                            </a>
                            
                            <!-- Groupe collé Côte à Côte : Volet 1, Volet 2 & Volet 3 -->
                            <div class="grid grid-cols-3 rounded-2xl overflow-hidden shadow-lg border border-white/20 divide-x divide-white/20">
                                <a :href="`/verify/${type}/${act.uuid}/download?volet=1`" target="_blank" 
                                   title="Exemplaire conservé au Centre d'État Civil (Mairie)"
                                   class="py-2.5 bg-emerald-950/80 hover:bg-emerald-900 text-emerald-100 hover:text-white active:scale-95 transition-all flex flex-col items-center justify-center cursor-pointer text-center px-1">
                                    <div class="flex items-center gap-1">
                                        <ArrowDownTrayIcon class="h-3.5 w-3.5 text-emerald-400 shrink-0" />
                                        <span class="font-black text-[11px] uppercase tracking-tight">Volet 1</span>
                                    </div>
                                    <span class="text-[9px] text-emerald-300/80 font-bold uppercase tracking-wider mt-0.5">Mairie</span>
                                </a>
                                <a :href="`/verify/${type}/${act.uuid}/download?volet=2`" target="_blank" 
                                   title="Exemplaire transmis au Greffe du Tribunal d'Instance"
                                   class="py-2.5 bg-slate-900/90 hover:bg-slate-800 text-slate-100 hover:text-white active:scale-95 transition-all flex flex-col items-center justify-center cursor-pointer text-center px-1">
                                    <div class="flex items-center gap-1">
                                        <ArrowDownTrayIcon class="h-3.5 w-3.5 text-amber-400 shrink-0" />
                                        <span class="font-black text-[11px] uppercase tracking-tight">Volet 2</span>
                                    </div>
                                    <span class="text-[9px] text-amber-300/80 font-bold uppercase tracking-wider mt-0.5">Tribunal</span>
                                </a>
                                <a :href="`/verify/${type}/${act.uuid}/download?volet=3`" target="_blank" 
                                   title="Exemplaire remis au Titulaire / Déclarant"
                                   class="py-2.5 bg-indigo-950/90 hover:bg-indigo-900 text-indigo-100 hover:text-white active:scale-95 transition-all flex flex-col items-center justify-center cursor-pointer text-center px-1">
                                    <div class="flex items-center gap-1">
                                        <ArrowDownTrayIcon class="h-3.5 w-3.5 text-indigo-300 shrink-0" />
                                        <span class="font-black text-[11px] uppercase tracking-tight">Volet 3</span>
                                    </div>
                                    <span class="text-[9px] text-indigo-300/80 font-bold uppercase tracking-wider mt-0.5">Titulaire</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Custom Status Confirmation Modal -->
            <Teleport to="body">
              <Transition
                enter-active-class="transition duration-200 ease-out"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition duration-150 ease-in"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
              >
                <div v-if="showStatusModal" class="fixed inset-0 z-50 overflow-y-auto bg-gray-900/60 backdrop-blur-sm flex items-center justify-center p-4">
                  <div class="bg-white rounded-3xl overflow-hidden shadow-2xl transform transition-all max-w-lg w-full border border-gray-100">
                    <div class="h-2 w-full" :class="{
                      'bg-green-600': pendingStatus === 'valide',
                      'bg-emerald-600': pendingStatus === 'signe',
                      'bg-amber-500': pendingStatus === 'a_corriger',
                      'bg-red-600': pendingStatus === 'rejete'
                    }"></div>

                    <div class="p-8">
                      <div class="flex items-start gap-4">
                        <div class="flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-2xl" :class="{
                          'bg-green-50 text-green-600': pendingStatus === 'valide',
                          'bg-emerald-50 text-emerald-600': pendingStatus === 'signe',
                          'bg-amber-50 text-amber-600': pendingStatus === 'a_corriger',
                          'bg-red-50 text-red-600': pendingStatus === 'rejete'
                        }">
                          <component :is="getStatusModalIcon()" class="h-6 w-6" />
                        </div>

                        <div class="flex-1 min-w-0">
                          <h3 class="text-lg font-black text-gray-900 leading-6 tracking-tight mb-2">
                            {{ statusModalTitle }}
                          </h3>
                          <p class="text-sm font-semibold text-gray-500">
                            {{ statusModalDescription }}
                          </p>
                        </div>
                      </div>

                      <div class="mt-8 flex justify-end gap-3 pt-6 border-t border-gray-50">
                        <button 
                          type="button" 
                          @click="showStatusModal = false" 
                          class="px-6 py-3 bg-white border border-gray-200 rounded-2xl text-xs font-black text-gray-500 uppercase tracking-widest hover:bg-gray-50 transition-all cursor-pointer"
                        >
                          Annuler
                        </button>
                        <button 
                          type="button" 
                          @click="confirmStatusChange" 
                          class="px-6 py-3 rounded-2xl text-xs font-black uppercase tracking-widest shadow-lg transition-all flex items-center justify-center gap-2 cursor-pointer"
                          :class="statusModalConfirmClass"
                        >
                          {{ statusModalConfirmText }}
                        </button>
                      </div>
                    </div>
                  </div>
                </div>
              </Transition>
            </Teleport>
        </div>
    </AuthenticatedLayout>
</template>
