<script setup>
import { computed, watch } from 'vue'
import { 
    CheckCircleIcon, 
    XCircleIcon, 
    ExclamationTriangleIcon, 
    InformationCircleIcon,
    XMarkIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
    isOpen: Boolean,
    type: {
        type: String,
        default: 'success' // success, error, warning, info
    },
    title: String,
    message: String,
    buttonText: {
        type: String,
        default: ''
    }
})

const emit = defineEmits(['close'])

const icon = computed(() => {
    switch (props.type) {
        case 'success': return CheckCircleIcon
        case 'error': return XCircleIcon
        case 'warning': return ExclamationTriangleIcon
        default: return InformationCircleIcon
    }
})

const colors = computed(() => {
    switch (props.type) {
        case 'success': return 'bg-green-50 text-green-600'
        case 'error': return 'bg-red-50 text-red-600'
        case 'warning': return 'bg-yellow-50 text-yellow-600'
        default: return 'bg-blue-50 text-blue-600'
    }
})

const buttonClass = computed(() => {
    switch (props.type) {
        case 'success': return 'bg-green-600 hover:bg-green-700 shadow-xl shadow-green-100'
        case 'error': return 'bg-red-600 hover:bg-red-700 shadow-xl shadow-red-100'
        case 'warning': return 'bg-yellow-500 hover:bg-yellow-600 shadow-xl shadow-yellow-100'
        default: return 'bg-blue-600 hover:bg-blue-700 shadow-xl shadow-blue-100'
    }
})

const defaultButtonText = computed(() => {
    if (props.buttonText) return props.buttonText
    switch (props.type) {
        case 'success': return 'Continuer'
        case 'error': return 'Fermer'
        case 'warning': return 'Fermer'
        default: return 'OK'
    }
})

// Auto-close success messages after 5 seconds
watch(() => props.isOpen, (newVal) => {
    if (newVal && props.type === 'success') {
        setTimeout(() => {
            emit('close')
        }, 5000)
    }
})
</script>

<template>
    <transition
        enter-active-class="transition duration-200 ease-out"
        enter-from-class="opacity-0 scale-95"
        enter-to-class="opacity-100 scale-100"
        leave-active-class="transition duration-150 ease-in"
        leave-from-class="opacity-100 scale-100"
        leave-to-class="opacity-0 scale-95"
    >
        <div v-if="isOpen" class="fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-6 overflow-hidden">
            <!-- Backdrop -->
            <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm" @click="emit('close')"></div>

            <!-- Modal Container -->
            <div class="relative bg-white rounded-3xl shadow-2xl border border-gray-100 w-full max-w-lg overflow-hidden transform transition-all">
                
                <div class="p-8 flex items-start gap-6">
                    <!-- Icon Box -->
                    <div :class="['flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-2xl', colors]">
                        <component :is="icon" class="h-6 w-6" />
                    </div>

                    <!-- Content -->
                    <div class="flex-1 min-w-0">
                        <h3 class="text-lg font-black text-gray-900 leading-tight">
                            {{ title || (type === 'success' ? 'Opération Réussie' : type === 'error' ? 'Erreur' : 'Notification') }}
                        </h3>
                        
                        <p class="mt-2 text-sm text-gray-500 font-medium">
                            {{ message }}
                        </p>

                        <!-- Action Button -->
                        <div class="mt-8 flex justify-end">
                            <button
                                @click="emit('close')"
                                :class="['px-6 py-3 rounded-xl font-black text-[10px] uppercase tracking-widest transition-all active:scale-95 text-white', buttonClass]"
                            >
                                {{ defaultButtonText }}
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Close X Button -->
                <button 
                    @click="emit('close')"
                    class="absolute top-6 right-6 p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-50 rounded-xl transition-all"
                >
                    <XMarkIcon class="h-5 w-5" />
                </button>
            </div>
        </div>
    </transition>
</template>
