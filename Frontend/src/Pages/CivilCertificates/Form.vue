<script setup>
import { useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

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
    <AuthenticatedLayout title="Nouveau Registre">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ is_edit ? 'Modifier l\'Acte : ' + certificate.reference_number : 'Établir un Nouveau Certificat' }}
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white shadow-xl rounded-lg p-8">
                    <form @submit.prevent="submit" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Basic Info -->
                            <div class="col-span-full">
                                <label class="block text-sm font-semibold text-gray-700">Type de Certificat</label>
                                <select v-model="form.type" :disabled="is_edit" class="mt-1 block w-full px-4 py-3 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold disabled:bg-gray-50 disabled:text-gray-500">
                                    <option v-for="t in types" :key="t" :value="t">{{ formatType(t) }}</option>
                                </select>
                                <p v-if="form.errors.type" class="text-red-500 text-xs mt-1 font-bold">{{ form.errors.type }}</p>
                                <p v-if="is_edit" class="mt-1 text-[10px] text-gray-400 italic">Le type d'acte ne peut pas être modifié après création.</p>
                            </div>

                            <div class="col-span-full">
                                <label class="block text-sm font-semibold text-gray-700">Centre d'État Civil / Localité <span class="text-red-500">*</span></label>
                                <input v-model="form.center" type="text" placeholder="Ex: Centre de Kolda, Dakar Plateau" class="mt-1 block w-full px-4 py-3 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold" required />
                                <p v-if="form.errors.center" class="text-red-500 text-xs mt-1 font-bold">{{ form.errors.center }}</p>
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700">Prénom du Demandeur <span class="text-red-500">*</span></label>
                                <input v-model="form.applicant_first_name" type="text" class="mt-1 block w-full px-4 py-3 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold" required />
                                <p v-if="form.errors.applicant_first_name" class="text-red-500 text-xs mt-1 font-bold">{{ form.errors.applicant_first_name }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700">Nom du Demandeur <span class="text-red-500">*</span></label>
                                <input v-model="form.applicant_last_name" type="text" class="mt-1 block w-full px-4 py-3 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold" required />
                                <p v-if="form.errors.applicant_last_name" class="text-red-500 text-xs mt-1 font-bold">{{ form.errors.applicant_last_name }}</p>
                            </div>
                            
                            <div class="col-span-full">
                                <label class="block text-sm font-semibold text-gray-700">Numéro CNI {{ form.type === 'residence' ? '(OBLIGATOIRE)' : '(Optionnel)' }} <span v-if="form.type === 'residence'" class="text-red-500">*</span></label>
                                <input v-model="form.applicant_cni" type="text" class="mt-1 block w-full px-4 py-3 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold" :required="form.type === 'residence'" />
                                <p v-if="form.errors.applicant_cni" class="text-red-500 text-xs mt-1 font-bold">{{ form.errors.applicant_cni }}</p>
                            </div>
                        </div>

                        <div class="border-t border-gray-100 pt-6">
                            <h3 class="text-lg font-medium text-gray-800 mb-4 flex items-center">
                                <span class="text-white text-xs font-black px-3 py-1.5 rounded mr-2" style="background-color: #1E690F;">DONNÉES SPÉCIFIQUES</span>
                                Informations exigées pour ce type d'acte
                            </h3>
                            
                            <!-- Dynamic fields based on type -->
                            <div v-if="form.type === 'residence'" class="space-y-4">
                                <div>
                                    <label class="text-xs font-bold text-gray-500 uppercase">Adresse précise au sein de la commune <span class="text-red-500">*</span></label>
                                    <input v-model="form.data.adresse" type="text" placeholder="Quartier, Rue, N° de porte..." class="mt-1 w-full px-4 py-3 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold" required />
                                    <p v-if="form.errors['data.adresse']" class="text-red-500 text-xs mt-1 font-bold">{{ form.errors['data.adresse'] }}</p>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="text-xs font-bold text-gray-500 uppercase">Identité Témoin 1 <span class="text-red-500">*</span></label>
                                        <input v-model="form.data.temoin_1_identite" type="text" placeholder="Prénom, Nom, CNI" class="mt-1 w-full px-4 py-3 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold" required />
                                        <p v-if="form.errors['data.temoin_1_identite']" class="text-red-500 text-xs mt-1 font-bold">{{ form.errors['data.temoin_1_identite'] }}</p>
                                    </div>
                                    <div>
                                        <label class="text-xs font-bold text-gray-500 uppercase">Identité Témoin 2 <span class="text-red-500">*</span></label>
                                        <input v-model="form.data.temoin_2_identite" type="text" placeholder="Prénom, Nom, CNI" class="mt-1 w-full px-4 py-3 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold" required />
                                        <p v-if="form.errors['data.temoin_2_identite']" class="text-red-500 text-xs mt-1 font-bold">{{ form.errors['data.temoin_2_identite'] }}</p>
                                    </div>
                                </div>
                            </div>

                            <div v-if="form.type === 'coutume'" class="space-y-4">
                                <input v-model="form.data.lieu_coutume" type="text" placeholder="Lieu de coutume" class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold" required />
                                <p v-if="form.errors['data.lieu_coutume']" class="text-red-500 text-xs mt-1 font-bold">{{ form.errors['data.lieu_coutume'] }}</p>
                                
                                <input v-model="form.data.date_naissance" type="date" class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold" required />
                                <p v-if="form.errors['data.date_naissance']" class="text-red-500 text-xs mt-1 font-bold">{{ form.errors['data.date_naissance'] }}</p>
                                
                                <select v-model="form.data.sexe" class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold" required>
                                    <option value="" disabled selected>Sélectionner le Sexe</option>
                                    <option value="M">Masculin</option>
                                    <option value="F">Féminin</option>
                                </select>
                                <p v-if="form.errors['data.sexe']" class="text-red-500 text-xs mt-1 font-bold">{{ form.errors['data.sexe'] }}</p>
                                
                                <textarea v-model="form.data.temoins" placeholder="Identité des 2 témoins" class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold" required></textarea>
                                <p v-if="form.errors['data.temoins']" class="text-red-500 text-xs mt-1 font-bold">{{ form.errors['data.temoins'] }}</p>
                            </div>

                            <div v-if="form.type === 'indigence'" class="space-y-4">
                                <div>
                                    <label class="text-xs font-bold text-gray-500 uppercase">Ressources Mensuelles (FCFA)</label>
                                    <input v-model="form.data.ressources_mensuelles" type="number" step="100" class="mt-1 w-full px-4 py-3 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold" required />
                                    <p v-if="form.errors['data.ressources_mensuelles']" class="text-red-500 text-xs mt-1 font-bold">{{ form.errors['data.ressources_mensuelles'] }}</p>
                                </div>
                                <div>
                                    <label class="text-xs font-bold text-gray-500 uppercase">Composition du foyer & Détails</label>
                                    <textarea v-model="form.data.composition_foyer" placeholder="Ex: Chef de famille, 4 enfants à charge..." class="mt-1 w-full px-4 py-3 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold h-24" required></textarea>
                                    <p v-if="form.errors['data.composition_foyer']" class="text-red-500 text-xs mt-1 font-bold">{{ form.errors['data.composition_foyer'] }}</p>
                                </div>
                                <div class="bg-red-50 p-3 rounded border border-red-100">
                                    <p class="text-[10px] text-red-700 font-bold uppercase">Attention: La signature électronique de l'Officier est légalement requise pour ce certificat.</p>
                                </div>
                            </div>

                            <div v-if="form.type === 'vie_collective' || form.type === 'vie_individuel' || form.type === 'individualite'" class="space-y-4">
                                <textarea v-model="form.data.motif" placeholder="Motif officiel de la demande" class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold" required></textarea>
                                <p v-if="form.errors['data.motif']" class="text-red-500 text-xs mt-1 font-bold">{{ form.errors['data.motif'] }}</p>
                                
                                <textarea v-if="form.type === 'vie_collective'" v-model="form.data.membres_identites" placeholder="Liste complète des membres concernés" class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold h-32" required></textarea>
                                <p v-if="form.errors['data.membres_identites']" class="text-red-500 text-xs mt-1 font-bold">{{ form.errors['data.membres_identites'] }}</p>
                            </div>

                            <div v-if="form.type === 'non_inscrit_naissance' || form.type === 'acte_non_inexistant'" class="space-y-4">
                                <input v-model="form.data.date_naissance" type="date" class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold" required />
                                <p v-if="form.errors['data.date_naissance']" class="text-red-500 text-xs mt-1 font-bold">{{ form.errors['data.date_naissance'] }}</p>
                                
                                <textarea v-model="form.data.periode_recherche" placeholder="Période ou registre de recherche" class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] font-bold" required></textarea>
                                <p v-if="form.errors['data.periode_recherche']" class="text-red-500 text-xs mt-1 font-bold">{{ form.errors['data.periode_recherche'] }}</p>
                                
                                <div class="bg-blue-50 p-3 rounded border border-blue-100">
                                    <p class="text-xs text-blue-700 italic">SYSTÈME: Recherche automatisée dans le registre numérique (SIG-EC) activée au clic sur Soumettre.</p>
                                </div>
                            </div>
                        </div>

                        <div class="pt-6">
                            <button type="submit" 
                                    :disabled="form.processing"
                                    class="w-full inline-flex justify-center py-3 px-4 border border-transparent shadow-sm text-sm font-bold rounded-md text-white hover:bg-[#185709] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#1E690F] disabled:bg-gray-400"
                                    style="background-color: #1E690F;">
                                <svg v-if="form.processing" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                {{ form.processing ? 'Soumission en cours...' : (is_edit ? 'Mettre à Jour et Enregistrer' : 'Soumettre et Mettre à Jour le Registre') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
