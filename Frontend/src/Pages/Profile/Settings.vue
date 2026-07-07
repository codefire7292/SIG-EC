<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head } from '@inertiajs/vue3'
import { 
    Cog6ToothIcon,
    BellIcon,
    PaintBrushIcon,
    GlobeAltIcon,
    ShieldCheckIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
    user: Object
})
</script>

<template>
    <Head title="Paramètres" />

    <AuthenticatedLayout>
        <div class="max-w-4xl mx-auto py-12 px-4">
            <header class="mb-12">
                <div class="flex items-center gap-4 mb-4">
                    <div class="h-12 w-12 bg-green-50 text-[#1E690F] rounded-2xl flex items-center justify-center">
                        <Cog6ToothIcon class="h-6 w-6" />
                    </div>
                    <div>
                        <h1 class="text-3xl font-black text-gray-900 tracking-tight">Paramètres</h1>
                        <p class="text-gray-500 font-medium">Personnalisez votre expérience sur la plateforme.</p>
                    </div>
                </div>
            </header>

            <div class="space-y-6">
                <!-- Appearance -->
                <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-gray-100">
                    <div class="flex items-center justify-between mb-8">
                        <div class="flex items-center gap-4">
                            <div class="h-10 w-10 bg-orange-50 text-orange-600 rounded-xl flex items-center justify-center">
                                <PaintBrushIcon class="h-5 w-5" />
                            </div>
                            <div>
                                <h3 class="font-black text-gray-900">Apparence</h3>
                                <p class="text-xs text-gray-400 font-bold uppercase tracking-widest">Thème & Interface</p>
                            </div>
                        </div>
                        <div class="flex bg-gray-50 p-1 rounded-xl">
                            <button class="px-4 py-2 bg-white text-[#1E690F] rounded-lg shadow-sm font-black text-xs">Clair</button>
                            <button class="px-4 py-2 text-gray-400 font-black text-xs hover:text-gray-600">Sombre</button>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-3 gap-4">
                        <div class="border-2 border-[#1E690F] rounded-2xl p-4 cursor-pointer bg-green-50/50">
                            <div class="h-12 bg-white rounded-lg shadow-sm mb-3"></div>
                            <p class="text-[10px] font-black text-center text-[#1E690F] uppercase tracking-widest">Moderne</p>
                        </div>
                        <div class="border-2 border-transparent hover:border-gray-200 rounded-2xl p-4 cursor-pointer bg-gray-50/50 transition-all">
                            <div class="h-12 bg-gray-200 rounded-lg mb-3"></div>
                            <p class="text-[10px] font-black text-center text-gray-400 uppercase tracking-widest">Classique</p>
                        </div>
                        <div class="border-2 border-transparent hover:border-gray-200 rounded-2xl p-4 cursor-pointer bg-gray-50/50 transition-all">
                            <div class="h-12 bg-gray-300 rounded-lg mb-3"></div>
                            <p class="text-[10px] font-black text-center text-gray-400 uppercase tracking-widest">Compact</p>
                        </div>
                    </div>
                </div>

                <!-- Notifications -->
                <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-gray-100">
                    <div class="flex items-center gap-4 mb-8">
                        <div class="h-10 w-10 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center">
                            <BellIcon class="h-5 w-5" />
                        </div>
                        <div>
                            <h3 class="font-black text-gray-900">Notifications</h3>
                            <p class="text-xs text-gray-400 font-bold uppercase tracking-widest">Alertes & Emails</p>
                        </div>
                    </div>
                    
                    <div class="space-y-4">
                        <div v-for="label in ['Alertes de sécurité', 'Rapports hebdomadaires', 'Nouveaux messages']" :key="label" class="flex items-center justify-between p-4 bg-gray-50/50 rounded-2xl border border-gray-50">
                             <span class="font-bold text-sm text-gray-700">{{ label }}</span>
                             <div class="w-12 h-6 bg-[#1E690F] rounded-full relative cursor-pointer">
                                 <div class="absolute right-1 top-1 h-4 w-4 bg-white rounded-full transition-all"></div>
                             </div>
                        </div>
                    </div>
                </div>

                <!-- Localization -->
                <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-gray-100">
                    <div class="flex items-center gap-4 mb-8">
                        <div class="h-10 w-10 bg-green-50 text-green-600 rounded-xl flex items-center justify-center">
                            <GlobeAltIcon class="h-5 w-5" />
                        </div>
                        <div>
                            <h3 class="font-black text-gray-900">Langue & Région</h3>
                            <p class="text-xs text-gray-400 font-bold uppercase tracking-widest">Fuseau horaire</p>
                        </div>
                    </div>
                    
                    <select class="w-full bg-white border border-gray-300 rounded-2xl font-bold py-4 px-6 focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F]">
                        <option>Français (Sénégal)</option>
                        <option>Wolof</option>
                        <option>English (US)</option>
                    </select>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
