<script setup>
import { ref, watch, onMounted } from 'vue'
import { usePage } from '@inertiajs/vue3'
import Sidebar from '@/Components/Sidebar.vue'
import Topbar from '@/Components/Topbar.vue'
import AlertModal from '@/Components/AlertModal.vue'

const isSidebarOpen = ref(false)
const page = usePage()

const alertState = ref({
    type: 'success',
    title: '',
    message: ''
})

function toggleSidebar() {
    isSidebarOpen.value = !isSidebarOpen.value
}

function closeAlert() {
    alertState.value.message = ''
}

// Global helper for components
onMounted(() => {
    window.platformAlert = (message, type = 'success', title = '') => {
        alertState.value = {
            type: type,
            title: title || (type === 'success' ? 'Succès' : type === 'error' ? 'Erreur' : 'Information'),
            message: message
        }
    }
})

// Watch for flash messages
watch(() => page.props.flash, (flash) => {
    if (flash.success) {
        alertState.value = {
            type: 'success',
            title: 'Opération Réussie',
            message: flash.success
        }
    } else if (flash.warning) {
        alertState.value = {
            type: 'warning',
            title: 'Attention',
            message: flash.warning
        }
    } else if (flash.error) {
        alertState.value = {
            type: 'error',
            title: 'Une erreur est survenue',
            message: flash.error
        }
    }
}, { deep: true, immediate: true })
</script>


<template>
    <div class="min-h-screen bg-gray-50 flex">
        <!-- Sidebar -->
        <Sidebar :is-open="isSidebarOpen" @toggle-sidebar="toggleSidebar" />

        <!-- Zone de contenu principale -->
        <div class="flex-1 flex flex-col lg:ml-64 transition-all duration-300">
            <!-- Topbar -->
            <Topbar @toggle-sidebar="toggleSidebar" />

            <!-- En-tête de page (optionnel) -->
            <header v-if="$slots.header" class="bg-white border-b py-5 px-4 sm:px-6 lg:px-8" style="border-color: #D9EDD0;">
                <slot name="header" />
            </header>

            <!-- Alerte globale -->
            <AlertModal 
                :is-open="!!alertState.message"
                :type="alertState.type"
                :title="alertState.title"
                :message="alertState.message"
                @close="closeAlert"
            />

            <!-- Contenu de la page -->
            <main class="flex-1 p-4 sm:p-6 lg:p-8">
                <slot />
            </main>

            <!-- Footer -->
            <footer class="py-4 px-8 border-t flex items-center justify-between bg-white" style="border-color: #D9EDD0;">
                <div class="flex items-center gap-2">
                    <div class="h-6 w-6 rounded-full border flex items-center justify-center" style="border-color: #F0C31E;">
                        <img src="/images/logo.png" alt="Mairie de Enampore" class="h-4 w-4 object-contain" />
                    </div>
                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Mairie de Enampore — SIG-EC</span>
                </div>
                <span class="text-[10px] text-gray-300 font-medium">
                    © {{ new Date().getFullYear() }} Système de Gestion de l'État Civil
                </span>
            </footer>
        </div>

        <!-- Overlay mobile -->
        <div 
            v-if="isSidebarOpen" 
            @click="isSidebarOpen = false"
            class="fixed inset-0 z-40 bg-brand-900/60 backdrop-blur-sm lg:hidden"
        ></div>
    </div>
</template>
