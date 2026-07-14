<script setup>
import { Link } from '@inertiajs/vue3'
</script>

<template>
    <div class="min-h-screen bg-gray-50 flex flex-col font-sans">
        <!-- Header public officiel -->
        <header class="fixed top-0 left-0 right-0 z-[60] bg-white/95 backdrop-blur-md shadow-sm py-2 px-4" style="border-bottom: 2px solid #1E690F;">
            <div class="max-w-7xl mx-auto flex items-center justify-between">
                <!-- Logo + Nom -->
                <Link :href="$page.props.auth.user ? route('dashboard') : '/'" class="flex items-center gap-3 group">
                    <div class="h-12 w-12 rounded-full bg-white flex items-center justify-center border-2 shadow-sm group-hover:scale-105 transition-transform duration-200" style="border-color: #F0C31E;">
                        <img 
                            src="/images/logo.png" 
                            alt="Mairie de Enampore" 
                            class="h-9 w-9 object-contain"
                        />
                    </div>
                    <div class="flex flex-col leading-tight">
                        <span class="text-base font-black tracking-tight leading-none" style="color: #1E690F;">Mairie de Enampore</span>
                        <span class="text-[9px] font-black uppercase tracking-[0.4em] leading-none mt-0.5" style="color: #F0C31E;">Système État Civil</span>
                    </div>
                </Link>
                
                <!-- République du Sénégal — badge discret -->
                <div class="hidden md:flex flex-col items-center">
                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-[0.3em]">République du Sénégal</span>
                    <span class="text-[9px] text-gray-300 tracking-[0.2em]">Commune de Enampore</span>
                </div>

                <div class="flex items-center gap-3">
                    <Link 
                        v-if="!$page.props.auth.user"
                        :href="route('login')" 
                        class="px-5 py-2.5 text-white rounded-xl text-xs font-black shadow-brand hover:opacity-90 active:scale-95 transition-all duration-200 flex items-center gap-2"
                        style="background: linear-gradient(135deg, #1E690F 0%, #3D9426 100%);"
                    >
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" />
                        </svg>
                        Accès Officier
                    </Link>
                    <Link 
                        v-else
                        :href="route('civil-certificates.index')" 
                        class="px-5 py-2.5 rounded-xl text-xs font-black border transition-all duration-200"
                        style="background: #F2F9EE; color: #1E690F; border-color: #B3D9A0;"
                    >
                        Accéder aux Registres
                    </Link>
                </div>
            </div>
        </header>

        <!-- Contenu principal -->
        <main class="flex-1 mt-20">
            <slot />
        </main>

        <!-- Footer institutionnel -->
        <footer class="py-10 border-t" style="background: linear-gradient(180deg, #F2F9EE 0%, #D9EDD0 100%); border-color: #B3D9A0;">
            <div class="max-w-7xl mx-auto px-4">
                <div class="flex flex-col md:flex-row items-center justify-between gap-6">
                    <div class="flex items-center gap-4">
                        <div class="h-14 w-14 rounded-full bg-white flex items-center justify-center border-2 shadow-sm" style="border-color: #F0C31E;">
                            <img src="/images/logo.png" alt="Mairie de Enampore" class="h-10 w-10 object-contain" />
                        </div>
                        <div>
                            <p class="font-black text-sm" style="color: #1E690F;">Mairie de Enampore — SIG-EC</p>
                            <p class="text-xs text-gray-500">Solution intégrée de gestion de l'État Civil</p>
                        </div>
                    </div>
                    <div class="text-center">
                        <p class="text-xs text-gray-500 font-medium">République du Sénégal — Commune de Enampore</p>
                    </div>
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-[0.4em]">
                        © {{ new Date().getFullYear() }} SIG-EC
                    </p>
                </div>
            </div>
        </footer>
    </div>
</template>
