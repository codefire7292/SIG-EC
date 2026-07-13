<template>
  <AuthenticatedLayout title="Vérification de Certificat">
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Détails du Registre : {{ certificate.reference_number }}
      </h2>
    </template>

    <div class="py-12">
      <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <!-- Messages flash -->
        <div v-if="$page.props.flash && $page.props.flash.success" class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
          <span class="block sm:inline">{{ $page.props.flash.success }}</span>
        </div>
        <div v-if="$page.props.flash && $page.props.flash.error" class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
          <span class="block sm:inline">{{ $page.props.flash.error }}</span>
        </div>

        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
            <div class="flex justify-between items-start mb-6">
                <div>
                   <h3 class="text-2xl font-bold text-gray-900 mb-2">{{ formatType(certificate.type) }}</h3>
                   <p class="text-sm text-gray-500">
                     Statut: 
                     <span :class="{
                        'px-2 py-0.5 rounded text-xs font-bold uppercase': true,
                        'bg-gray-100 text-gray-800': certificate.status === 'brouillon',
                        'bg-yellow-100 text-yellow-800': certificate.status === 'observation',
                        'bg-orange-100 text-orange-800': certificate.status === 'a_corriger',
                        'bg-blue-100 text-blue-800': certificate.status === 'valide_hierarchie',
                        'bg-green-100 text-green-800': certificate.status === 'signe'
                     }">
                        {{ formatStatus(certificate.status) }}
                     </span>
                   </p>
                </div>
                <div v-if="certificate.status === 'signe'" class="bg-blue-50 text-blue-700 px-3 py-1 rounded border border-blue-200 text-xs font-bold">
                    ACTE SÉCURISÉ & ARCHIVÉ
                </div>
            </div>

            <div v-if="certificate.officer_comments" class="mb-6 p-4 bg-orange-50 border-l-4 border-orange-400 rounded-r-md">
                <h4 class="text-xs font-black text-orange-800 uppercase mb-1">Commentaires de l'Autorité (Observation)</h4>
                <p class="text-sm text-orange-700">{{ certificate.officer_comments }}</p>
            </div>

            <p class="text-xs text-gray-400 mb-6 italic">Enregistré le {{ new Date(certificate.created_at).toLocaleString() }} 
              <span v-if="certificate.version_number > 1" class="ml-2 font-bold text-indigo-600">(Version {{ certificate.version_number }} - Rectifiée)</span>
            </p>

            <div v-if="certificate.rectification_reason" class="mb-6 p-4 bg-indigo-50 border-l-4 border-indigo-400 rounded-r-md">
                <h4 class="text-xs font-black text-indigo-800 uppercase mb-1">Motif de Rectification (Mention Marginale)</h4>
                <p class="text-sm text-indigo-700">{{ certificate.rectification_reason }}</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
              
              <!-- Identité -->
              <div>
                <dt class="text-sm font-medium text-gray-500">Prénom de l'intéressé</dt>
                <dd class="mt-1 text-sm text-gray-900 font-semibold">{{ certificate.applicant_first_name }}</dd>
              </div>
              <div>
                <dt class="text-sm font-medium text-gray-500">Nom de l'intéressé</dt>
                <dd class="mt-1 text-sm text-gray-900 font-semibold">{{ certificate.applicant_last_name }}</dd>
              </div>
              <div v-if="certificate.applicant_cni">
                <dt class="text-sm font-medium text-gray-500">Numéro CNI</dt>
                <dd class="mt-1 text-sm text-gray-900 font-mono">{{ certificate.applicant_cni }}</dd>
              </div>
              <div v-if="certificate.center">
                <dt class="text-sm font-medium text-gray-500">Centre d'État Civil</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ certificate.center }}</dd>
              </div>

              <div class="md:col-span-2 my-4 border-t border-gray-100"></div>

              <!-- Dynamic Data Fields -->
              <div v-for="(value, key) in certificate.data" :key="key" class="capitalize">
                <dt class="text-sm font-medium text-gray-500">{{ key.replace(/_/g, ' ') }}</dt>
                <dd class="mt-1 text-sm text-gray-900 whitespace-pre-wrap">{{ value || 'N/A' }}</dd>
              </div>
            </div>

            <!-- Actions d'Administration -->
            <div class="mt-10 pt-6 border-t border-gray-200 flex flex-wrap gap-3 justify-end">              <!-- Rectification (OFFICIER - If Signed) -->
              <button v-if="can.rectify"
                @click="openModal('rectify')"
                :disabled="loading"
                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
              >
                Rectifier (Mention Marginale)
              </button>

              <!-- Observe (Officier/Responsable) -->
              <button v-if="can.observe && certificate.status !== 'signe' && certificate.status !== 'brouillon'"
                @click="openModal('observe')"
                :disabled="loading"
                class="inline-flex items-center px-4 py-2 bg-yellow-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-600 active:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2 transition ease-in-out duration-150"
              >
                Mettre en Observation
              </button>

              <!-- Request Correction -->
              <button v-if="can.observe && certificate.status === 'observation'"
                @click="openModal('request-correction')"
                :disabled="loading"
                class="inline-flex items-center px-4 py-2 bg-orange-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-orange-700 active:bg-orange-800 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 transition ease-in-out duration-150"
              >
                Renvoyer pour Correction
              </button>

              <!-- Signature (OFFICIER) -->
              <button v-if="can.sign && certificate.status === 'valide_hierarchie'"
                @click="openModal('sign')"
                :disabled="loading"
                class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150"
              >
                Apposer Signature Finale
              </button>

              <!-- Validation (Responsable/Directeur) -->
              <button v-if="can.approve && certificate.status !== 'signe' && certificate.status !== 'valide_hierarchie'"
                @click="openModal('approve')"
                :disabled="loading"
                class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 active:bg-green-900 focus:outline-none focus:border-green-900 focus:ring ring-green-300 disabled:opacity-25 transition ease-in-out duration-150"
              >
                <span v-if="loading">Traitement...</span>
                <span v-else>Valider vers Hiérarchie</span>
              </button>

              <!-- Voir PDF (Si signé) -->
              <a v-if="certificate.status === 'signe' && certificate.pdf_path"
                 :href="certificate.pdf_path.startsWith('/storage') ? certificate.pdf_path : '/storage/' + certificate.pdf_path"
                 target="_blank"
                 class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-900 active:bg-black focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150"
              >
                Ouvrir le Registre Numérique (PDF)
              </a>

              <!-- Modifier (If allowed) -->
              <Link v-if="can.update"
                :href="route('civil-certificates.edit', certificate.id)"
                class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150"
              >
                Modifier les données
              </Link>
            </div>

            <!-- Historique des Mentions Marginales (Rectifications) -->
            <div v-if="versions && versions.length > 1" class="mt-12 pt-8 border-t-2 border-dashed border-gray-100">
                <h4 class="text-sm font-black text-indigo-900 uppercase mb-4 flex items-center">
                    <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                    Historique des Mentions Marginales (Versions)
                </h4>
                <div class="overflow-hidden bg-white border border-gray-200 rounded-lg shadow-sm">
                    <table class="min-w-full divide-y divide-gray-200 text-[11px]">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-3 py-2 text-left font-bold text-gray-500 uppercase">Version</th>
                                <th class="px-3 py-2 text-left font-bold text-gray-500 uppercase">Générée le</th>
                                <th class="px-3 py-2 text-left font-bold text-gray-500 uppercase">Motif / État</th>
                                <th class="px-3 py-2 text-right font-bold text-gray-500 uppercase">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr v-for="v in versions" :key="v.id" :class="{'bg-blue-50': v.id === certificate.id}">
                                <td class="px-3 py-2 font-black">V{{ v.version_number }} <span v-if="v.is_current" class="ml-1 text-[8px] bg-green-100 text-green-700 px-1 rounded">Actuelle</span></td>
                                <td class="px-3 py-2">{{ new Date(v.created_at).toLocaleDateString() }}</td>
                                <td class="px-3 py-2 text-gray-500 italic">{{ v.rectification_reason || 'Version Initiale' }}</td>
                                <td class="px-3 py-2 text-right">
                                    <Link v-if="v.id !== certificate.id" :href="route('civil-certificates.show', v.id)" class="text-indigo-600 font-bold hover:underline">Consulter</Link>
                                    <span v-else class="text-gray-400">En vue</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Traçabilité (Audit Logs) -->
            <div class="mt-12 pt-8 border-t-2 border-dashed border-gray-100">
                <h4 class="text-sm font-black text-gray-900 uppercase mb-4 flex items-center">
                    <svg class="h-4 w-4 mr-2 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Journal de Traçabilité (Registre d'Audit)
                </h4>
                <div class="space-y-4">
                    <div v-for="log in audit_logs" :key="log.id" class="flex gap-4 p-3 bg-gray-50 rounded-lg border border-gray-100">
                        <div class="text-[10px] font-bold text-gray-400 w-24 tabular-nums">
                            {{ new Date(log.created_at).toLocaleDateString() }}<br>
                            {{ new Date(log.created_at).toLocaleTimeString() }}
                        </div>
                        <div class="flex-1">
                            <span class="text-xs font-black text-gray-900 uppercase tracking-tighter">{{ log.action }}</span>
                            <p class="text-[11px] text-gray-500">Par: <span class="font-bold text-gray-700">{{ log.user?.name || 'Système' }}</span></p>
                            <div v-if="log.metadata?.ip" class="text-[9px] text-gray-400 font-mono">ID: {{ log.metadata.ip }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Custom Premium Modal -->
            <Teleport to="body">
              <Transition
                enter-active-class="transition duration-200 ease-out"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition duration-150 ease-in"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
              >
                <div v-if="showModal" class="fixed inset-0 z-50 overflow-y-auto bg-gray-900/60 backdrop-blur-sm flex items-center justify-center p-4">
                  <div 
                    class="bg-white rounded-3xl overflow-hidden shadow-2xl transform transition-all max-w-lg w-full border border-gray-100"
                  >
                    <!-- Top border colored decorator -->
                    <div class="h-2 w-full" :class="{
                      'bg-green-600': modalType === 'approve',
                      'bg-blue-600': modalType === 'sign',
                      'bg-yellow-500': modalType === 'observe',
                      'bg-orange-600': modalType === 'request-correction',
                      'bg-indigo-600': modalType === 'rectify'
                    }"></div>

                    <div class="p-8">
                      <div class="flex items-start gap-4">
                        <div class="flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-2xl" :class="{
                          'bg-green-50 text-green-600': modalType === 'approve',
                          'bg-blue-50 text-blue-600': modalType === 'sign',
                          'bg-yellow-50 text-yellow-600': modalType === 'observe',
                          'bg-orange-50 text-orange-600': modalType === 'request-correction',
                          'bg-indigo-50 text-indigo-600': modalType === 'rectify'
                        }">
                          <component :is="getModalIcon()" class="h-6 w-6" />
                        </div>

                        <div class="flex-1 min-w-0">
                          <h3 class="text-lg font-black text-gray-900 leading-6 tracking-tight mb-2">
                            {{ modalTitle }}
                          </h3>
                          <p class="text-sm font-semibold text-gray-500 mb-6">
                            {{ modalDescription }}
                          </p>

                          <!-- Form Inputs inside Modal -->
                          <div v-if="isInputRequired" class="space-y-2 mb-4">
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest pl-1">
                              {{ modalInputLabel }}
                            </label>
                            <textarea 
                              v-model="modalInputValue" 
                              rows="4" 
                              :placeholder="modalInputPlaceholder"
                              class="w-full px-4 py-3 rounded-2xl border border-gray-200 bg-gray-50/50 focus:outline-none focus:ring-2 focus:ring-opacity-50 font-medium text-sm transition-all focus:bg-white resize-none"
                              :class="{
                                'focus:ring-yellow-500 focus:border-yellow-500': modalType === 'observe',
                                'focus:ring-indigo-600 focus:border-indigo-600': modalType === 'rectify'
                              }"
                            ></textarea>
                            <p v-if="modalInputError" class="text-xs text-red-500 font-bold pl-1">
                              {{ modalInputError }}
                            </p>
                          </div>
                        </div>
                      </div>

                      <!-- Footer Action Buttons -->
                      <div class="mt-8 flex justify-end gap-3 pt-6 border-t border-gray-50">
                        <button 
                          type="button" 
                          @click="showModal = false" 
                          class="px-6 py-3 bg-white border border-gray-200 rounded-2xl text-xs font-black text-gray-500 uppercase tracking-widest hover:bg-gray-50 transition-all focus:outline-none"
                        >
                          Annuler
                        </button>
                        <button 
                          type="button" 
                          @click="handleConfirm" 
                          class="px-6 py-3 rounded-2xl text-xs font-black uppercase tracking-widest shadow-lg transition-all focus:outline-none focus:ring-2 focus:ring-offset-2 flex items-center justify-center gap-2"
                          :class="modalConfirmButtonClass"
                        >
                          {{ modalConfirmButtonText }}
                        </button>
                      </div>
                    </div>
                  </div>
                </div>
              </Transition>
            </Teleport>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { 
  CheckCircleIcon, 
  CheckBadgeIcon, 
  EyeIcon, 
  ArrowPathIcon, 
  PencilSquareIcon 
} from '@heroicons/vue/24/outline';

const props = defineProps({
  certificate: Object,
  audit_logs: Array,
  versions: Array,
  can: Object,
});

const loading = ref(false);

// Modal state variables
const showModal = ref(false);
const modalType = ref(''); // 'approve' | 'sign' | 'observe' | 'request-correction' | 'rectify'
const modalTitle = ref('');
const modalDescription = ref('');
const modalInputLabel = ref('');
const modalInputValue = ref('');
const modalInputPlaceholder = ref('');
const modalConfirmButtonText = ref('');
const modalConfirmButtonClass = ref('');
const isInputRequired = ref(false);
const modalInputError = ref('');

const getModalIcon = () => {
  if (modalType.value === 'approve') return CheckCircleIcon;
  if (modalType.value === 'sign') return CheckBadgeIcon;
  if (modalType.value === 'observe') return EyeIcon;
  if (modalType.value === 'request-correction') return ArrowPathIcon;
  if (modalType.value === 'rectify') return PencilSquareIcon;
  return CheckCircleIcon;
};

const openModal = (type) => {
    modalType.value = type;
    modalInputValue.value = '';
    modalInputError.value = '';
    isInputRequired.value = false;
    
    if (type === 'approve') {
        modalTitle.value = 'Validation vers la hiérarchie';
        modalDescription.value = "Confirmer la validation vers la hiérarchie ? L'acte sera examiné par l'autorité supérieure.";
        modalConfirmButtonText.value = 'Valider';
        modalConfirmButtonClass.value = 'bg-green-600 hover:bg-green-700 text-white focus:ring-green-500';
    } else if (type === 'sign') {
        modalTitle.value = 'Signature finale et scellage';
        modalDescription.value = "Voulez-vous procéder à la SIGNATURE FINALE ? Après cette action, l'acte deviendra IMMUABLE et sera archivé au registre numérique.";
        modalConfirmButtonText.value = 'Signer et Sceller';
        modalConfirmButtonClass.value = 'bg-blue-600 hover:bg-blue-700 text-white focus:ring-blue-500';
    } else if (type === 'observe') {
        modalTitle.value = 'Mise en observation';
        modalDescription.value = "Saisissez vos observations pour l'agent afin qu'il puisse corriger ou compléter l'acte.";
        modalInputLabel.value = 'Observations / Remarques';
        modalInputPlaceholder.value = 'Ex : Le nom de famille de l\'époux comporte une faute de frappe...';
        isInputRequired.value = true;
        modalConfirmButtonText.value = 'Enregistrer';
        modalConfirmButtonClass.value = 'bg-yellow-500 hover:bg-yellow-600 text-white focus:ring-yellow-500';
    } else if (type === 'request-correction') {
        modalTitle.value = 'Renvoyer pour correction';
        modalDescription.value = "Renvoyer cet acte à l'agent pour correction selon les observations ?";
        modalConfirmButtonText.value = 'Renvoyer à l\'agent';
        modalConfirmButtonClass.value = 'bg-orange-600 hover:bg-orange-700 text-white focus:ring-orange-500';
    } else if (type === 'rectify') {
        modalTitle.value = 'Rectification (Mention Marginale)';
        modalDescription.value = "Saisissez le MOTIF LÉGAL de la rectification (Mention Marginale). Cela créera une nouvelle version de l'acte basée sur celle-ci. L'original sera conservé.";
        modalInputLabel.value = 'Motif légal de la rectification';
        modalInputPlaceholder.value = 'Ex : Suite au jugement de rectification n° X du Tribunal d\'Instance...';
        isInputRequired.value = true;
        modalConfirmButtonText.value = 'Rectifier';
        modalConfirmButtonClass.value = 'bg-indigo-600 hover:bg-indigo-700 text-white focus:ring-indigo-500';
    }
    showModal.value = true;
};

const handleConfirm = () => {
    modalInputError.value = '';
    
    if (isInputRequired.value && (!modalInputValue.value || modalInputValue.value.trim().length === 0)) {
        modalInputError.value = 'Ce champ est obligatoire.';
        return;
    }
    
    if (modalType.value === 'rectify' && modalInputValue.value.trim().length < 10) {
        modalInputError.value = 'Le motif doit être plus explicite (min. 10 caractères).';
        return;
    }
    
    showModal.value = false;
    loading.value = true;
    
    const finish = () => { loading.value = false; };

    if (modalType.value === 'approve') {
        router.post(route('civil-certificates.approve', props.certificate.id), {}, { onFinish: finish });
    } else if (modalType.value === 'sign') {
        router.post(route('civil-certificates.sign', props.certificate.id), {}, { onFinish: finish });
    } else if (modalType.value === 'observe') {
        router.post(route('civil-certificates.observe', props.certificate.id), { comments: modalInputValue.value }, { onFinish: finish });
    } else if (modalType.value === 'request-correction') {
        router.post(route('civil-certificates.request-correction', props.certificate.id), {}, { onFinish: finish });
    } else if (modalType.value === 'rectify') {
        router.post(route('civil-certificates.rectify', props.certificate.id), {
            reason: modalInputValue.value,
            data: props.certificate.data
        }, { onFinish: finish });
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

const formatStatus = (status) => {
  const dictionary = {
    brouillon: 'Brouillon',
    observation: 'Sous Observation',
    a_corriger: 'À corriger',
    valide_hierarchie: 'Validé Hiérarchie',
    signe: 'Signé / Archivé',
  };
  return dictionary[status] || status;
};
</script>
