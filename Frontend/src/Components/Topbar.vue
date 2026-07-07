<script setup>
import { Link, usePage } from '@inertiajs/vue3'
import { ref } from 'vue'
import { 
    Bars3Icon, 
    UserCircleIcon,
    ChevronDownIcon,
    ArrowRightOnRectangleIcon
} from '@heroicons/vue/24/outline'

const emit = defineEmits(['toggleSidebar'])
const page = usePage()

const isProfileOpen = ref(false)
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
                <div class="relative">
                    <button 
                        @click="isProfileOpen = !isProfileOpen"
                        class="flex items-center gap-3 p-1 rounded-full hover:bg-brand-50 transition-colors focus:outline-none pr-3 border border-transparent hover:border-brand-100"
                    >
                        <div class="h-9 w-9 rounded-full overflow-hidden border-2 shadow-sm flex items-center justify-center bg-brand-50" style="border-color: #1E690F;">
                             <UserCircleIcon class="h-full w-full" style="color: #1E690F;" />
                        </div>
                        <div class="hidden sm:block text-left">
                            <p class="text-sm font-bold text-gray-800 leading-none">{{ page.props.auth.user.name }}</p>
                            <p class="text-[10px] mt-0.5 uppercase font-black tracking-tighter" style="color: #1E690F;">Officier d'État Civil</p>
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
