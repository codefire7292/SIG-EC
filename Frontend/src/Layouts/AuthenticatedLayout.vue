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

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col lg:ml-64 transition-all duration-300">
            <!-- Topbar -->
            <Topbar @toggle-sidebar="toggleSidebar" />

            <!-- Page Header (Optional) -->
            <header v-if="$slots.header" class="bg-white border-b border-gray-100 py-6 px-4 sm:px-6 lg:px-8">
                <slot name="header" />
            </header>

            <!-- Global Alert Modal -->
            <AlertModal 
                :is-open="!!alertState.message"
                :type="alertState.type"
                :title="alertState.title"
                :message="alertState.message"
                @close="closeAlert"
            />

            <!-- Page Content -->
            <main class="flex-1 p-4 sm:p-6 lg:p-8">
                <slot />
            </main>

            <!-- Optional Footer -->
            <footer class="py-6 px-8 border-t border-gray-200 text-center text-sm text-gray-400">
                &copy; {{ new Date().getFullYear() }} SIG-EC - Système Intégré de Gestion de l'État Civil.
            </footer>
        </div>

        <!-- Mobile Overlay -->
        <div 
            v-if="isSidebarOpen" 
            @click="isSidebarOpen = false"
            class="fixed inset-0 z-40 bg-gray-900 bg-opacity-50 lg:hidden"
        ></div>
    </div>
</template>
