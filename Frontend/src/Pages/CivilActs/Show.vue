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
    PlusCircleIcon
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
                            <div v-if="type === 'naissance'" class="grid grid-cols-2 gap-8">
                                <div>
                                    <h4 class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Enfant</h4>
                                    <div class="text-xl font-black text-gray-900">{{ act.first_name }} {{ act.last_name }}</div>
                                    <div class="text-sm font-bold text-blue-600">{{ act.gender === 'M' ? 'Masculin' : 'Féminin' }}</div>
                                </div>
                                <div>
                                    <h4 class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Naissance</h4>
                                    <div class="text-sm font-black text-gray-900">{{ formatDate(act.date_of_birth) }}</div>
                                    <div class="text-xs font-bold text-gray-500 italic">{{ act.place_of_birth }}</div>
                                </div>
                                <div v-if="act.is_judgment" class="col-span-full p-4 bg-green-50/50 rounded-2xl border border-green-100 flex flex-wrap gap-6 items-center">
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
                                </div>
                                <div class="col-span-full pt-4 border-t border-gray-50 grid grid-cols-2 gap-8">
                                    <div>
                                        <h4 class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Père</h4>
                                        <div class="text-sm font-bold text-gray-700">{{ act.father_name || 'Non renseigné' }}</div>
                                    </div>
                                    <div>
                                        <h4 class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Mère</h4>
                                        <div class="text-sm font-bold text-gray-700">{{ act.mother_name || 'Non renseignée' }}</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Mariage Context -->
                            <div v-if="type === 'mariage'" class="space-y-8">
                                <div class="grid grid-cols-2 gap-8 p-6 bg-gray-50 rounded-2xl border border-gray-100">
                                    <div class="text-center">
                                        <div class="text-[9px] font-black text-blue-600 uppercase mb-2">Épou</div>
                                        <div class="text-lg font-black text-gray-900">{{ act.husband_first_name }} {{ act.husband_last_name }}</div>
                                    </div>
                                    <div class="text-center border-l border-gray-200">
                                        <div class="text-[9px] font-black text-pink-600 uppercase mb-2">Épouse</div>
                                        <div class="text-lg font-black text-gray-900">{{ act.wife_first_name }} {{ act.wife_last_name }}</div>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
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
                                        </div>
                                    </div>
                                    <div>
                                        <h4 class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Régime Matrimonial</h4>
                                        <div class="text-sm font-black text-[#1E690F] uppercase tracking-tight">
                                            {{ act.matrimonial_regime === 'separation_biens' ? 'Séparation des biens' : (act.matrimonial_regime === 'regime_dotal' ? 'Régime dotal' : (act.matrimonial_regime === 'participation_meubles_acquets' ? 'Participation aux meubles et acquêts' : act.matrimonial_regime || 'Séparation des biens')) }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Deces Context -->
                            <div v-if="type === 'deces'" class="grid grid-cols-2 gap-8">
                                <div class="col-span-full">
                                    <h4 class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Défunt</h4>
                                    <div class="text-xl font-black text-gray-900">{{ act.deceased_first_name }} {{ act.deceased_last_name }}</div>
                                </div>
                                <div>
                                    <h4 class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Décès</h4>
                                    <div class="text-sm font-black text-gray-900">{{ formatDate(act.date_of_death) }}</div>
                                    <div class="text-xs font-bold text-gray-500 italic">{{ act.place_of_death }}</div>
                                </div>
                                <div>
                                    <h4 class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Cause</h4>
                                    <div class="text-sm font-bold text-gray-700">{{ act.cause_of_death || 'Non renseignée' }}</div>
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
                                <h4 class="text-[9px] font-black text-gray-400 uppercase mb-2">Observations de l'officier</h4>
                                <p class="text-sm text-gray-700 italic">"{{ act.officer_comments }}"</p>
                            </div>

                            <div v-if="type === 'naissance' && act.parents_metadata" class="grid grid-cols-2 gap-6">
                                <div v-if="act.parents_metadata.father_profession">
                                    <h4 class="text-[9px] font-black text-gray-400 uppercase mb-1">Profession du Père</h4>
                                    <p class="text-xs font-bold text-gray-900">{{ act.parents_metadata.father_profession }}</p>
                                </div>
                                <div v-if="act.parents_metadata.mother_profession">
                                    <h4 class="text-[9px] font-black text-gray-400 uppercase mb-1">Profession de la Mère</h4>
                                    <p class="text-xs font-bold text-gray-900">{{ act.parents_metadata.mother_profession }}</p>
                                </div>
                                <div v-if="act.parents_metadata.residence" class="col-span-full">
                                    <h4 class="text-[9px] font-black text-gray-400 uppercase mb-1">Domicile des parents</h4>
                                    <p class="text-xs font-bold text-gray-900">{{ act.parents_metadata.residence }}</p>
                                </div>
                            </div>

                            <div v-if="type === 'mariage' && act.witnesses_metadata" class="grid grid-cols-2 gap-6">
                                <div v-if="act.witnesses_metadata.witness1_name" class="p-4 bg-gray-50/50 rounded-2xl border border-gray-100">
                                    <h4 class="text-[9px] font-black text-gray-400 uppercase mb-1">Témoin 1</h4>
                                    <p class="text-sm font-black text-gray-900">{{ act.witnesses_metadata.witness1_name }}</p>
                                    <p v-if="act.witnesses_metadata.witness1_cni" class="text-xs text-gray-500 font-medium mt-1">CNI : {{ act.witnesses_metadata.witness1_cni }}</p>
                                </div>
                                <div v-if="act.witnesses_metadata.witness2_name" class="p-4 bg-gray-50/50 rounded-2xl border border-gray-100">
                                    <h4 class="text-[9px] font-black text-gray-400 uppercase mb-1">Témoin 2</h4>
                                    <p class="text-sm font-black text-gray-900">{{ act.witnesses_metadata.witness2_name }}</p>
                                    <p v-if="act.witnesses_metadata.witness2_cni" class="text-xs text-gray-500 font-medium mt-1">CNI : {{ act.witnesses_metadata.witness2_cni }}</p>
                                </div>
                                <div v-if="act.witnesses_metadata.witness3_name" class="p-4 bg-gray-50/50 rounded-2xl border border-gray-100">
                                    <h4 class="text-[9px] font-black text-gray-400 uppercase mb-1">Témoin 3</h4>
                                    <p class="text-sm font-black text-gray-900">{{ act.witnesses_metadata.witness3_name }}</p>
                                    <p v-if="act.witnesses_metadata.witness3_cni" class="text-xs text-gray-500 font-medium mt-1">CNI : {{ act.witnesses_metadata.witness3_cni }}</p>
                                </div>
                                <div v-if="act.witnesses_metadata.witness4_name" class="p-4 bg-gray-50/50 rounded-2xl border border-gray-100">
                                    <h4 class="text-[9px] font-black text-gray-400 uppercase mb-1">Témoin 4</h4>
                                    <p class="text-sm font-black text-gray-900">{{ act.witnesses_metadata.witness4_name }}</p>
                                    <p v-if="act.witnesses_metadata.witness4_cni" class="text-xs text-gray-500 font-medium mt-1">CNI : {{ act.witnesses_metadata.witness4_cni }}</p>
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
