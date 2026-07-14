<script setup>
import { Link, usePage } from '@inertiajs/vue3'
import { ref } from 'vue'
import { 
    Bars3Icon, 
    UserCircleIcon,
    ChevronDownIcon,
    ArrowRightOnRectangleIcon,
    BellIcon
} from '@heroicons/vue/24/outline'
import { BellIcon as BellIconSolid } from '@heroicons/vue/24/solid'

const emit = defineEmits(['toggleSidebar'])
const page = usePage()

const isProfileOpen = ref(false)
const isNotificationsOpen = ref(false)

const markNotificationsAsRead = () => {
    isNotificationsOpen.value = false;
    if (page.props.auth.user.unreadNotifications.length > 0) {
        import('@inertiajs/vue3').then(({ router }) => {
            router.post(route('notifications.mark-as-read'), {}, { preserveScroll: true });
        });
    }
}

</script>

<template>
    <header class="h-16 bg-white sticky top-0 z-40 shadow-sm" style="border-bottom: 2px solid #1E690F;">
        <div class="px-4 sm:px-6 lg:px-8 h-full flex items-center justify-between">
            <!-- Menu burger mobile -->
            <div class="flex items-center gap-3 lg:hidden">
                <button 
                    @click="emit('toggleSidebar')"
                    class="p-2 rounded-xl hover:bg-brand-50 focus:outline-none transition-colors"
                    style="color: #1E690F;"
                >
                    <Bars3Icon class="h-6 w-6" />
                </button>
                <!-- Logo mobile -->
                <div class="flex items-center gap-2">
                    <img src="/images/logo.png" alt="Mairie de Enampore" class="h-8 w-8 object-contain" />
                    <div class="flex flex-col leading-none">
                        <span class="text-sm font-black tracking-tight" style="color: #1E690F;">Mairie de Enampore</span>
                        <span class="text-[8px] font-bold uppercase tracking-widest" style="color: #F0C31E;">État Civil</span>
                    </div>
                </div>
            </div>

            <!-- Titre contextuel desktop -->
            <div class="hidden lg:flex items-center gap-2">
                <div class="h-2 w-2 rounded-full animate-pulse" style="background: #1E690F;"></div>
                <span class="text-xs font-bold uppercase tracking-widest" style="color: #1E690F;">Système en ligne</span>
            </div>

            <!-- Profil à droite -->
            <div class="flex-1 flex justify-end items-center gap-4">
                
                <!-- Notifications -->
                <div class="relative">
                    <button 
                        @click="isNotificationsOpen = !isNotificationsOpen"
                        class="p-2 rounded-full hover:bg-brand-50 transition-colors focus:outline-none relative"
                    >
                        <BellIcon class="h-6 w-6 text-gray-600 hover:text-brand-700" />
                        <span v-if="page.props.auth.user.unreadNotifications?.length > 0" class="absolute top-1.5 right-2 h-2.5 w-2.5 bg-red-500 rounded-full ring-2 ring-white"></span>
                    </button>

                    <Transition
                        enter-active-class="transition duration-150 ease-out"
                        enter-from-class="transform scale-95 opacity-0"
                        enter-to-class="transform scale-100 opacity-100"
                        leave-active-class="transition duration-100 ease-in"
                        leave-from-class="transform scale-100 opacity-100"
                        leave-to-class="transform scale-95 opacity-0"
                    >
                        <div 
                            v-if="isNotificationsOpen" 
                            class="absolute right-0 mt-2 w-80 bg-white rounded-2xl shadow-lg py-2 z-50 overflow-hidden border"
                            style="border-color: #D9EDD0;"
                        >
                            <div class="px-4 py-3 border-b border-gray-50 flex items-center justify-between">
                                <h3 class="text-sm font-black text-gray-900">Notifications</h3>
                                <button v-if="page.props.auth.user.unreadNotifications?.length > 0" @click="markNotificationsAsRead" class="text-[10px] font-bold text-brand-600 uppercase tracking-widest hover:underline">
                                    Tout marquer lu
                                </button>
                            </div>
                            <div class="max-h-64 overflow-y-auto">
                                <template v-if="page.props.auth.user.unreadNotifications?.length > 0">
                                    <div v-for="notification in page.props.auth.user.unreadNotifications" :key="notification.id" class="px-4 py-3 hover:bg-gray-50 border-b border-gray-50/50">
                                        <Link :href="notification.data.url" @click="markNotificationsAsRead" class="block">
                                            <p class="text-xs font-bold text-gray-900 mb-0.5">{{ notification.data.title }}</p>
                                            <p class="text-[10px] text-gray-600 leading-tight">{{ notification.data.message }}</p>
                                        </Link>
                                    </div>
                                </template>
                                <div v-else class="px-4 py-6 text-center">
                                    <BellIcon class="h-8 w-8 text-gray-300 mx-auto mb-2" />
                                    <p class="text-xs text-gray-500">Aucune notification</p>
                                </div>
                            </div>
                        </div>
                    </Transition>
                </div>

                <!-- Profil -->
                <div class="relative">
                    <button 
                        @click="isProfileOpen = !isProfileOpen"
                        class="flex items-center gap-3 p-1 rounded-full hover:bg-brand-50 transition-colors focus:outline-none pr-3 border border-transparent hover:border-brand-100"
                    >
                        <div class="h-9 w-9 rounded-full overflow-hidden border-2 shadow-sm flex items-center justify-center bg-brand-50" style="border-color: #1E690F;">
                             <img v-if="page.props.auth.user.profile_photo_url" :src="page.props.auth.user.profile_photo_url" class="h-full w-full object-cover" />
                             <UserCircleIcon v-else class="h-full w-full" style="color: #1E690F;" />
                        </div>
                        <div class="hidden sm:block text-left">
                            <p class="text-sm font-bold text-gray-800 leading-none">{{ page.props.auth.user.name }}</p>
                            <p class="text-[10px] mt-0.5 uppercase font-black tracking-tighter" style="color: #1E690F;">{{ page.props.auth.user.role || 'Officier d\'État Civil' }}</p>
                        </div>
                        <ChevronDownIcon class="h-4 w-4 text-gray-400 transition-transform duration-200" :class="isProfileOpen ? 'rotate-180' : ''" />
                    </button>

                    <Transition
                        enter-active-class="transition duration-150 ease-out"
                        enter-from-class="transform scale-95 opacity-0"
                        enter-to-class="transform scale-100 opacity-100"
                        leave-active-class="transition duration-100 ease-in"
                        leave-from-class="transform scale-100 opacity-100"
                        leave-to-class="transform scale-95 opacity-0"
                    >
                        <div 
                            v-if="isProfileOpen" 
                            class="absolute right-0 mt-2 w-52 bg-white rounded-2xl shadow-lg py-2 z-50 overflow-hidden border"
                            style="border-color: #D9EDD0;"
                        >
                            <div class="px-4 py-2 border-b border-gray-50 mb-1">
                                <p class="text-xs font-black text-gray-900 truncate">{{ page.props.auth.user.name }}</p>
                                <p class="text-[10px] font-bold uppercase tracking-widest" style="color: #1E690F;">{{ page.props.auth.user.role }}</p>
                            </div>
                            <Link :href="`/profile`" class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-700 hover:bg-brand-50 font-medium transition-colors">
                                <UserCircleIcon class="h-4 w-4" />
                                Mon profil
                            </Link>
                            <hr class="my-1 border-gray-100" />
                            <Link 
                                :href="`/logout`" 
                                method="post" 
                                as="button" 
                                class="w-full text-left flex items-center gap-2 px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 font-semibold transition-colors"
                            >
                                <ArrowRightOnRectangleIcon class="h-4 w-4" />
                                Déconnexion
                            </Link>
                        </div>
                    </Transition>
                </div>
            </div>
        </div>
    </header>
</template>
