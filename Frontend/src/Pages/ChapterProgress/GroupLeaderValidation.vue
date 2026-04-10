<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, router } from '@inertiajs/vue3'
import { 
    CheckCircleIcon, 
    AcademicCapIcon, 
    ArrowPathIcon,
    ShieldCheckIcon,
    XCircleIcon
} from '@heroicons/vue/24/outline'

import ConfirmModal from '@/Components/ConfirmModal.vue'
import { ref, computed } from 'vue'

const props = defineProps({
    group: Object,
    progress: Object
})

const modalState = ref({
    isOpen: false,
    title: '',
    message: '',
    type: 'info',
    confirmText: 'Confirmer',
    cancelText: 'Annuler',
    action: null
})

function openApproveModal(progressId) {
    modalState.value = {
        isOpen: true,
        title: 'Confirmer ce chapitre ?',
        message: "Êtes-vous sûr d'avoir bien suivi l'intégralité de ce chapitre avec votre formateur ?",
        type: 'success',
        confirmText: 'Oui, je confirme',
        cancelText: 'Annuler',
        action: () => {
            router.patch(route('chapter-progress.approve', progressId), {}, { preserveScroll: true })
            modalState.value.isOpen = false
        }
    }
}

function openRejectModal(progressId) {
    modalState.value = {
        isOpen: true,
        title: 'Rejeter ce chapitre ?',
        message: "Souhaitez-vous vraiment rejeter la soumission de ce chapitre ? Il sera renvoyé au formateur pour un nouvel examen.",
        type: 'danger',
        confirmText: 'Oui, rejeter',
        cancelText: 'Annuler',
        action: () => {
            router.patch(route('chapter-progress.reject', progressId), {}, { preserveScroll: true })
            modalState.value.isOpen = false
        }
    }
}

function handleConfirm() {
    if (modalState.value.action) modalState.value.action()
}

const pendingProgress = computed(() => {
    return Object.values(props.progress || {}).filter(p => p.status === 'pending')
})
</script>

<template>
    <Head title="Validation Pédagogique" />

    <AuthenticatedLayout>
        <div class="max-w-4xl mx-auto py-8 px-4">
            <header class="mb-8">
                <div class="flex items-center gap-3 mb-2">
                    <ShieldCheckIcon class="h-8 w-8 text-blue-600" />
                    <h1 class="text-3xl font-black text-gray-900 tracking-tight">Validation</h1>
                </div>
                <p class="text-gray-500">Valider les chapitres dispensés par le formateur pour le groupe <strong>{{ group.nom_groupe }}</strong>.</p>
            </header>

            <div class="space-y-4">
                <div v-if="pendingProgress.length === 0" class="bg-white p-12 rounded-3xl text-center border border-dashed border-gray-200">
                    <div class="h-16 w-16 bg-green-50 text-green-600 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <CheckCircleIcon class="h-8 w-8" />
                    </div>
                    <p class="text-gray-600 font-bold text-lg">Tout est à jour !</p>
                    <p class="text-gray-400 text-sm">Aucun chapitre en attente de validation pour le moment.</p>
                </div>

                <div 
                    v-for="item in pendingProgress" 
                    :key="item.id"
                    class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100 flex items-center justify-between group hover:border-blue-200 transition-all"
                >
                    <div class="flex items-center gap-6">
                        <div class="h-14 w-14 bg-yellow-50 text-yellow-600 rounded-2xl flex items-center justify-center shrink-0">
                            <AcademicCapIcon class="h-7 w-7" />
                        </div>
                        <div>
                            <h2 class="text-lg font-black text-gray-900 leading-tight">{{ item.chapter.titre }}</h2>
                            <div class="flex items-center gap-4 mt-1">
                                <p class="text-xs text-gray-400">Soumis par : <span class="text-gray-600 font-bold">{{ item.submitter.name }}</span></p>
                                <span class="h-1 w-1 rounded-full bg-gray-300"></span>
                                <span class="px-2 py-0.5 bg-yellow-50 text-yellow-700 text-[10px] font-black uppercase tracking-widest rounded-md border border-yellow-100">En attente</span>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <button 
                            @click="openRejectModal(item.id)"
                            class="px-4 py-3 bg-red-50 text-red-600 rounded-xl font-bold flex items-center gap-2 hover:bg-red-100 transition active:scale-95"
                            title="Indiquer que ce chapitre n'a pas été fait"
                        >
                            <XCircleIcon class="h-5 w-5" />
                            Rejeter
                        </button>
                        
                        <button 
                            @click="openApproveModal(item.id)"
                            class="px-6 py-3 bg-blue-600 text-white rounded-xl font-bold flex items-center gap-2 hover:bg-blue-700 hover:shadow-lg transition active:scale-95"
                        >
                            <CheckCircleIcon class="h-5 w-5" />
                            Valider
                        </button>
                    </div>
                </div>
            </div>

            <!-- Validation History (Optional but good for UX) -->
            <div class="mt-12 opacity-50">
                <h3 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-4">Historique des validations</h3>
                <!-- Could list approved items here -->
            </div>
        </div>

        <ConfirmModal 
            :is-open="modalState.isOpen"
            :title="modalState.title"
            :message="modalState.message"
            :type="modalState.type"
            :confirm-text="modalState.confirmText"
            :cancel-text="modalState.cancelText"
            @confirm="handleConfirm"
            @cancel="modalState.isOpen = false"
        />
    </AuthenticatedLayout>
</template>
