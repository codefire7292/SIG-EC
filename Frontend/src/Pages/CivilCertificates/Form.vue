<script setup>
import { useForm, Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {
    ChevronRightIcon,
    ChevronLeftIcon,
    DocumentCheckIcon,
    UserIcon,
    IdentificationIcon,
    BuildingOfficeIcon,
    DocumentTextIcon,
    SparklesIcon,
    CheckIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
    certificate: Object,
    type: String,
    types: Array,
    is_edit: Boolean,
});

const form = useForm({
    type: props.certificate?.type || props.type || 'residence',
    center: props.certificate?.center || 'DEF',
    applicant_first_name: props.certificate?.applicant_first_name || '',
    applicant_last_name: props.certificate?.applicant_last_name || '',
    applicant_cni: props.certificate?.applicant_cni || '',
    data: props.certificate?.data || {},
});

const submit = () => {
    if (props.is_edit) {
        form.patch(route('civil-certificates.update', props.certificate.id));
    } else {
        form.post(route('civil-certificates.store'));
    }
};

const formatType = (type) => {
    const dictionary = {
        residence: 'Certificat de résidence',
        coutume: 'Certificat de coutume',
        indigence: 'Certificat d\'indigence',
        individualite: 'Certificat d\'individualité',
        vie_collective: 'Certificat de vie collective',
        vie_individuel: 'Certificat de vie individuelle',
        non_inscrit_naissance: 'Certificat de non inscrit de naissance',
        acte_non_inexistant: 'Certificat d\'acte non inexistant',
    };
    return dictionary[type] || type;
};
</script>

<template>
    <Head :title="is_edit ? 'Modifier Certificat' : 'Nouveau Certificat'" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-2">
                <!-- Breadcrumb -->
                <div class="flex items-center gap-2 text-[10px] font-black uppercase tracking-widest text-gray-400">
                    <Link :href="route('civil-certificates.index')" class="hover:text-blue-600 transition-colors">Services Certificats</Link>
                    <ChevronRightIcon class="h-3 w-3 stroke-[2]" />
                    <Link :href="route('civil-certificates.index')" class="hover:text-blue-600 transition-colors">Certificats Civils</Link>
                    <ChevronRightIcon class="h-3 w-3 stroke-[2]" />
                    <span class="text-gray-600">{{ is_edit ? 'Modification' : 'Nouvelle Demande' }}</span>
                </div>

                <!-- Title row -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <div class="h-12 w-12 rounded-2xl flex items-center justify-center shadow-lg bg-gradient-to-br from-blue-600 to-indigo-700">
                            <DocumentCheckIcon class="h-6 w-6 text-white" />
                        </div>
                        <div>
                            <h2 class="font-black text-2xl text-gray-900 tracking-tight">
                                {{ is_edit ? 'Modifier le Certificat ' + (certificate?.reference_number || '') : 'Établir un Nouveau Certificat' }}
                            </h2>
                            <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Saisie et enregistrement de l'acte administratif</p>
                        </div>
                    </div>

                    <Link
                        :href="route('civil-certificates.index')"
                        class="inline-flex items-center justify-center px-5 py-3 bg-white border border-gray-200 rounded-2xl font-extrabold text-xs text-gray-700 hover:bg-gray-50 shadow-sm transition-all active:scale-95"
                    >
                        <ChevronLeftIcon class="w-4 h-4 mr-1.5 stroke-[2.5]" />
                        Retour à la liste
                    </Link>
                </div>
            </div>
        </template>

        <div class="max-w-4xl mx-auto space-y-8">
            <form @submit.prevent="submit" class="space-y-8">

                <!-- ── Section 1: Type & Localité ────────────────────────────── -->
                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8 space-y-6">
                    <div class="flex items-center gap-3 pb-4 border-b border-gray-100">
                        <div class="h-10 w-10 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600 font-black text-sm">
                            1
                        </div>
                        <div>
                            <h3 class="font-black text-base text-gray-900">Type de Certificat & Origine</h3>
                            <p class="text-xs text-gray-400 font-medium">Sélectionnez la catégorie d'acte et précisez le centre émetteur</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Type -->
                        <div>
                            <label class="block text-xs font-black text-gray-700 uppercase tracking-wider mb-2">
                                Type de Certificat <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <select
                                    v-model="form.type"
                                    :disabled="is_edit"
                                    class="w-full px-4 py-3.5 bg-gray-50/70 border border-gray-200 rounded-2xl text-xs font-bold text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:bg-white transition-all appearance-none cursor-pointer disabled:bg-gray-100 disabled:text-gray-500"
                                >
                                    <option v-for="t in types" :key="t" :value="t">
                                        {{ formatType(t) }}
                                    </option>
                                </select>
                                <ChevronRightIcon class="absolute right-4 top-1/2 -translate-y-1/2 h-4 w-4 rotate-90 text-gray-400 pointer-events-none stroke-[2]" />
                            </div>
                            <p v-if="form.errors.type" class="text-red-500 text-xs mt-1.5 font-bold">{{ form.errors.type }}</p>
                            <p v-if="is_edit" class="mt-1.5 text-[10px] text-gray-400 font-semibold italic">Le type d'acte ne peut pas être modifié après création.</p>
                        </div>

                        <!-- Center -->
                        <div>
                            <label class="block text-xs font-black text-gray-700 uppercase tracking-wider mb-2">
                                Centre d'État Civil / Localité <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <BuildingOfficeIcon class="absolute left-4 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400 pointer-events-none stroke-[2]" />
                                <input
                                    v-model="form.center"
                                    type="text"
                                    placeholder="Ex: DEF, Centre de Ziguinchor..."
                                    class="w-full pl-12 pr-4 py-3.5 bg-gray-50/70 border border-gray-200 rounded-2xl text-xs font-bold text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:bg-white transition-all"
                                    required
                                />
                            </div>
                            <p v-if="form.errors.center" class="text-red-500 text-xs mt-1.5 font-bold">{{ form.errors.center }}</p>
                        </div>
                    </div>
                </div>

                <!-- ── Section 2: Demandeur ─────────────────────────────────── -->
                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8 space-y-6">
                    <div class="flex items-center gap-3 pb-4 border-b border-gray-100">
                        <div class="h-10 w-10 rounded-xl bg-indigo-50 flex items-center justify-center text-indigo-600 font-black text-sm">
                            2
                        </div>
                        <div>
                            <h3 class="font-black text-base text-gray-900">Identité du Demandeur</h3>
                            <p class="text-xs text-gray-400 font-medium">Informations sur la personne faisant la demande du certificat</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- First Name -->
                        <div>
                            <label class="block text-xs font-black text-gray-700 uppercase tracking-wider mb-2">
                                Prénom du Demandeur <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <UserIcon class="absolute left-4 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400 pointer-events-none stroke-[2]" />
                                <input
                                    v-model="form.applicant_first_name"
                                    type="text"
                                    placeholder="Prénom..."
                                    class="w-full pl-12 pr-4 py-3.5 bg-gray-50/70 border border-gray-200 rounded-2xl text-xs font-bold text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:bg-white transition-all"
                                    required
                                />
                            </div>
                            <p v-if="form.errors.applicant_first_name" class="text-red-500 text-xs mt-1.5 font-bold">{{ form.errors.applicant_first_name }}</p>
                        </div>

                        <!-- Last Name -->
                        <div>
                            <label class="block text-xs font-black text-gray-700 uppercase tracking-wider mb-2">
                                Nom du Demandeur <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <UserIcon class="absolute left-4 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400 pointer-events-none stroke-[2]" />
                                <input
                                    v-model="form.applicant_last_name"
                                    type="text"
                                    placeholder="Nom..."
                                    class="w-full pl-12 pr-4 py-3.5 bg-gray-50/70 border border-gray-200 rounded-2xl text-xs font-bold text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:bg-white transition-all"
                                    required
                                />
                            </div>
                            <p v-if="form.errors.applicant_last_name" class="text-red-500 text-xs mt-1.5 font-bold">{{ form.errors.applicant_last_name }}</p>
                        </div>

                        <!-- CNI Number -->
                        <div class="col-span-full">
                            <label class="block text-xs font-black text-gray-700 uppercase tracking-wider mb-2">
                                Numéro CNI {{ form.type === 'residence' ? '(OBLIGATOIRE)' : '(Optionnel)' }}
                                <span v-if="form.type === 'residence'" class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <IdentificationIcon class="absolute left-4 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400 pointer-events-none stroke-[2]" />
                                <input
                                    v-model="form.applicant_cni"
                                    type="text"
                                    placeholder="Ex: 1 759 1998 01234"
                                    class="w-full pl-12 pr-4 py-3.5 bg-gray-50/70 border border-gray-200 rounded-2xl text-xs font-bold font-mono text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:bg-white transition-all"
                                    :required="form.type === 'residence'"
                                />
                            </div>
                            <p v-if="form.errors.applicant_cni" class="text-red-500 text-xs mt-1.5 font-bold">{{ form.errors.applicant_cni }}</p>
                        </div>
                    </div>
                </div>

                <!-- ── Section 3: Données Spécifiques ──────────────────────── -->
                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8 space-y-6">
                    <div class="flex items-center gap-3 pb-4 border-b border-gray-100">
                        <div class="h-10 w-10 rounded-xl bg-purple-50 flex items-center justify-center text-purple-600 font-black text-sm">
                            3
                        </div>
                        <div>
                            <h3 class="font-black text-base text-gray-900">Données Spécifiques</h3>
                            <p class="text-xs text-gray-400 font-medium">Champs spécifiques requis pour un {{ formatType(form.type) }}</p>
                        </div>
                    </div>

                    <!-- Residence -->
                    <div v-if="form.type === 'residence'" class="space-y-6">
                        <div>
                            <label class="block text-xs font-black text-gray-700 uppercase tracking-wider mb-2">
                                Adresse précise au sein de la commune <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model="form.data.adresse"
                                type="text"
                                placeholder="Quartier, Rue, N° de porte..."
                                class="w-full px-4 py-3.5 bg-gray-50/70 border border-gray-200 rounded-2xl text-xs font-bold text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:bg-white transition-all"
                                required
                            />
                            <p v-if="form.errors['data.adresse']" class="text-red-500 text-xs mt-1.5 font-bold">{{ form.errors['data.adresse'] }}</p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-xs font-black text-gray-700 uppercase tracking-wider mb-2">
                                    Identité Témoin 1 <span class="text-red-500">*</span>
                                </label>
                                <input
                                    v-model="form.data.temoin_1_identite"
                                    type="text"
                                    placeholder="Prénom, Nom, CNI"
                                    class="w-full px-4 py-3.5 bg-gray-50/70 border border-gray-200 rounded-2xl text-xs font-bold text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:bg-white transition-all"
                                    required
                                />
                                <p v-if="form.errors['data.temoin_1_identite']" class="text-red-500 text-xs mt-1.5 font-bold">{{ form.errors['data.temoin_1_identite'] }}</p>
                            </div>
                            <div>
                                <label class="block text-xs font-black text-gray-700 uppercase tracking-wider mb-2">
                                    Identité Témoin 2 <span class="text-red-500">*</span>
                                </label>
                                <input
                                    v-model="form.data.temoin_2_identite"
                                    type="text"
                                    placeholder="Prénom, Nom, CNI"
                                    class="w-full px-4 py-3.5 bg-gray-50/70 border border-gray-200 rounded-2xl text-xs font-bold text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:bg-white transition-all"
                                    required
                                />
                                <p v-if="form.errors['data.temoin_2_identite']" class="text-red-500 text-xs mt-1.5 font-bold">{{ form.errors['data.temoin_2_identite'] }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Coutume -->
                    <div v-if="form.type === 'coutume'" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <label class="block text-xs font-black text-gray-700 uppercase tracking-wider mb-2">Lieu de Coutume *</label>
                                <input v-model="form.data.lieu_coutume" type="text" placeholder="Lieu..." class="w-full px-4 py-3.5 bg-gray-50/70 border border-gray-200 rounded-2xl text-xs font-bold text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500" required />
                            </div>
                            <div>
                                <label class="block text-xs font-black text-gray-700 uppercase tracking-wider mb-2">Date de Naissance *</label>
                                <input v-model="form.data.date_naissance" type="date" class="w-full px-4 py-3.5 bg-gray-50/70 border border-gray-200 rounded-2xl text-xs font-bold text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500" required />
                            </div>
                            <div>
                                <label class="block text-xs font-black text-gray-700 uppercase tracking-wider mb-2">Sexe *</label>
                                <select v-model="form.data.sexe" class="w-full px-4 py-3.5 bg-gray-50/70 border border-gray-200 rounded-2xl text-xs font-bold text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                                    <option value="" disabled selected>Sélectionner</option>
                                    <option value="M">Masculin</option>
                                    <option value="F">Féminin</option>
                                </select>
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-black text-gray-700 uppercase tracking-wider mb-2">Identité des 2 témoins *</label>
                            <textarea v-model="form.data.temoins" placeholder="Saisir l'identité complète des témoins..." class="w-full px-4 py-3.5 bg-gray-50/70 border border-gray-200 rounded-2xl text-xs font-bold text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 h-24" required></textarea>
                        </div>
                    </div>

                    <!-- Indigence -->
                    <div v-if="form.type === 'indigence'" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-xs font-black text-gray-700 uppercase tracking-wider mb-2">Ressources Mensuelles (FCFA) *</label>
                                <input v-model="form.data.ressources_mensuelles" type="number" step="100" class="w-full px-4 py-3.5 bg-gray-50/70 border border-gray-200 rounded-2xl text-xs font-bold text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500" required />
                            </div>
                            <div>
                                <label class="block text-xs font-black text-gray-700 uppercase tracking-wider mb-2">Composition du foyer & Détails *</label>
                                <textarea v-model="form.data.composition_foyer" placeholder="Ex: Chef de famille, 4 enfants à charge..." class="w-full px-4 py-3.5 bg-gray-50/70 border border-gray-200 rounded-2xl text-xs font-bold text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 h-24" required></textarea>
                            </div>
                        </div>
                        <div class="bg-amber-50 p-4 rounded-2xl border border-amber-200/60 flex items-center gap-3">
                            <SparklesIcon class="h-5 w-5 text-amber-600 flex-shrink-0" />
                            <p class="text-xs text-amber-800 font-bold">La signature électronique de l'Officier d'état civil est obligatoirement requise pour ce certificat.</p>
                        </div>
                    </div>

                    <!-- Vie collective / individuel / individualite -->
                    <div v-if="['vie_collective', 'vie_individuel', 'individualite'].includes(form.type)" class="space-y-6">
                        <div>
                            <label class="block text-xs font-black text-gray-700 uppercase tracking-wider mb-2">Motif Officiel de la Demande *</label>
                            <textarea v-model="form.data.motif" placeholder="Saisir le motif officiel..." class="w-full px-4 py-3.5 bg-gray-50/70 border border-gray-200 rounded-2xl text-xs font-bold text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 h-24" required></textarea>
                        </div>

                        <div v-if="form.type === 'vie_collective'">
                            <label class="block text-xs font-black text-gray-700 uppercase tracking-wider mb-2">Liste des Membres Concernés *</label>
                            <textarea v-model="form.data.membres_identites" placeholder="Saisir les prénoms, noms et CNI de tous les membres..." class="w-full px-4 py-3.5 bg-gray-50/70 border border-gray-200 rounded-2xl text-xs font-bold text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 h-32" required></textarea>
                        </div>
                    </div>

                    <!-- Non-inscrit naissance / Acte non inexistant -->
                    <div v-if="['non_inscrit_naissance', 'acte_non_inexistant'].includes(form.type)" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-xs font-black text-gray-700 uppercase tracking-wider mb-2">Date de Naissance *</label>
                                <input v-model="form.data.date_naissance" type="date" class="w-full px-4 py-3.5 bg-gray-50/70 border border-gray-200 rounded-2xl text-xs font-bold text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500" required />
                            </div>
                            <div>
                                <label class="block text-xs font-black text-gray-700 uppercase tracking-wider mb-2">Période ou Registre de Recherche *</label>
                                <textarea v-model="form.data.periode_recherche" placeholder="Période recherchée..." class="w-full px-4 py-3.5 bg-gray-50/70 border border-gray-200 rounded-2xl text-xs font-bold text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 h-24" required></textarea>
                            </div>
                        </div>
                        <div class="bg-blue-50 p-4 rounded-2xl border border-blue-200/60 flex items-center gap-3">
                            <DocumentTextIcon class="h-5 w-5 text-blue-600 flex-shrink-0" />
                            <p class="text-xs text-blue-800 font-bold">Système: Recherche automatisée dans le registre numérique (SIG-EC) lors de la soumission.</p>
                        </div>
                    </div>
                </div>

                <!-- ── Submit Action Bar ───────────────────────────────────── -->
                <div class="flex items-center justify-end gap-4 pt-4">
                    <Link
                        :href="route('civil-certificates.index')"
                        class="px-6 py-4 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-2xl font-bold text-xs transition-all active:scale-95"
                    >
                        Annuler
                    </Link>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="px-8 py-4 bg-gradient-to-r from-blue-600 to-indigo-600 border border-transparent rounded-2xl font-black text-xs text-white uppercase tracking-widest hover:from-blue-700 hover:to-indigo-700 shadow-xl shadow-blue-200 transition-all active:scale-95 disabled:opacity-50 flex items-center gap-2"
                    >
                        <svg v-if="form.processing" class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <CheckIcon v-else class="h-4 w-4 stroke-[3]" />
                        {{ form.processing ? 'Enregistrement...' : (is_edit ? 'Mettre à jour le certificat' : 'Soumettre et Mettre à jour le Registre') }}
                    </button>
                </div>

            </form>
        </div>
    </AuthenticatedLayout>
</template>
