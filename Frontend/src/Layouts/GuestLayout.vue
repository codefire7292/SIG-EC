<script setup>
import { Link } from '@inertiajs/vue3'
</script>

<template>
    <div class="min-h-screen bg-white flex flex-col font-sans selection:bg-blue-500 selection:text-white">
        <header class="fixed top-0 left-0 right-0 z-[60] bg-white/80 backdrop-blur-md border-b border-gray-100 py-3 px-4">
            <div class="max-w-7xl mx-auto flex items-center justify-between">
                <Link :href="'/'" class="flex items-center gap-4 group">
                    <div class="flex flex-col">
                        <span class="text-2xl font-black text-blue-900 tracking-tighter leading-none italic">SIG-EC</span>
                        <span class="text-[8px] font-black text-blue-500 uppercase tracking-[0.4em]">Système État Civil</span>
                    </div>
                </Link>
                
                <div class="flex items-center gap-8">
                    <Link 
                        v-if="!$page.props.auth.user"
                        :href="route('login')" 
                        class="px-5 py-2.5 bg-blue-600 text-white rounded-xl text-xs font-black shadow-lg hover:bg-blue-700 transition-colors"
                    >
                        Accès Officier
                    </Link>
                    <Link 
                        v-else
                        :href="route('civil-certificates.index')" 
                        class="px-5 py-2.5 bg-gray-100 text-gray-900 rounded-xl text-xs font-black border border-gray-200 hover:bg-gray-200 transition"
                    >
                        Accéder aux Registres
                    </Link>
                </div>
            </div>
        </header>

        <main class="flex-1 mt-20">
            <slot />
        </main>

        <footer class="py-12 bg-gray-50 border-t border-gray-100">
            <div class="max-w-7xl mx-auto px-4 text-center">
                <div class="flex items-center justify-center gap-3 mb-4">
                    <span class="text-xl font-black text-blue-900 italic">SIG-EC</span>
                </div>
                <p class="text-gray-500 text-sm font-medium">
                    Solution intégrée de gestion de l'État Civil — République du Sénégal.
                </p>
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-[0.5em] mt-8">
                    © 2026 SIG-EC — Tous droits réservés.
                </p>
            </div>
        </footer>
    </div>
</template>
