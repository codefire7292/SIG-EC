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
    <header class="h-16 bg-white border-b border-gray-200 sticky top-0 z-40">
        <div class="px-4 sm:px-6 lg:px-8 h-full flex items-center justify-between">
            <div class="flex items-center lg:hidden">
                <button 
                    @click="emit('toggleSidebar')"
                    class="p-2 rounded-md text-gray-600 hover:bg-gray-100 focus:outline-none"
                >
                    <Bars3Icon class="h-6 w-6" />
                </button>
            </div>

            <div class="flex items-center gap-2 lg:hidden">
                 <span class="text-xl font-black text-blue-900 italic">SIG-EC</span>
            </div>

            <div class="flex-1 flex justify-end items-center gap-4">
                <!-- Profile Dropdown -->
                <div class="relative">
                    <button 
                        @click="isProfileOpen = !isProfileOpen"
                        class="flex items-center gap-3 p-1 rounded-full hover:bg-gray-50 transition focus:outline-none pr-3"
                    >
                        <div class="h-9 w-9 rounded-full overflow-hidden border-2 border-white shadow-sm bg-gray-100 ring-1 ring-gray-100">
                             <UserCircleIcon class="h-full w-full text-gray-300" />
                        </div>
                        <div class="hidden sm:block text-left">
                            <div class="flex items-center gap-1.5">
                                <p class="text-sm font-medium text-gray-700 leading-none">{{ page.props.auth.user.name }}</p>
                            </div>
                            <p class="text-[10px] text-gray-400 mt-0.5 uppercase font-bold tracking-tighter">Officier d'État Civil</p>
                        </div>
                        <ChevronDownIcon class="h-4 w-4 text-gray-400" />
                    </button>

                    <div 
                        v-if="isProfileOpen" 
                        class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-100 py-1 transition transform origin-top-right"
                    >
                        <Link :href="`/profile`" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Mon profil</Link>
                        <hr class="my-1 border-gray-100">
                        <Link 
                            :href="`/logout`" 
                            method="post" 
                            as="button" 
                            class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 flex items-center gap-2"
                        >
                            <ArrowRightOnRectangleIcon class="h-4 w-4" />
                            Déconnexion
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </header>
</template>
