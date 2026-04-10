<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link } from '@inertiajs/vue3'
import { 
    UsersIcon, 
    GlobeAltIcon, 
    BuildingOfficeIcon,
    CalendarIcon,
    MicrophoneIcon,
    ArrowRightIcon
} from '@heroicons/vue/24/outline'
import { formatTime, formatDate } from '@/utils/format'

const props = defineProps({
    partnerships: Array,
    events: Array,
    mediaMentions: Array
})
</script>

<template>
    <Head title="Écosystème & Rayonnement" />

    <AuthenticatedLayout>
        <div class="max-w-7xl mx-auto py-8 px-4">
            <header class="mb-12">
                <h1 class="text-4xl font-black text-gray-900 tracking-tight mb-2">Écosystème</h1>
                <p class="text-xl text-gray-500 font-medium tracking-tight">Vue d'ensemble du rayonnement et des partenariats du CRE.</p>
            </header>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
                <div class="bg-indigo-600 p-8 rounded-[2.5rem] text-white shadow-2xl shadow-indigo-200">
                    <div class="flex justify-between items-start mb-4">
                        <div class="h-12 w-12 bg-white/20 rounded-2xl flex items-center justify-center">
                            <BuildingOfficeIcon class="h-6 w-6" />
                        </div>
                    </div>
                    <div class="text-4xl font-black mb-1">{{ partnerships.length }}+</div>
                    <div class="text-indigo-100 font-bold uppercase tracking-widest text-xs">Partenaires Actifs</div>
                </div>

                <div class="bg-white p-8 rounded-[2.5rem] border border-gray-100 shadow-sm">
                    <div class="flex justify-between items-start mb-4">
                        <div class="h-12 w-12 bg-orange-100 text-orange-600 rounded-2xl flex items-center justify-center">
                            <CalendarIcon class="h-6 w-6" />
                        </div>
                    </div>
                    <div class="text-4xl font-black mb-1 text-gray-900">{{ events.length }}</div>
                    <div class="text-gray-400 font-bold uppercase tracking-widest text-xs">Événements réalisés</div>
                </div>

                <div class="bg-white p-8 rounded-[2.5rem] border border-gray-100 shadow-sm">
                    <div class="flex justify-between items-start mb-4">
                        <div class="h-12 w-12 bg-green-100 text-green-600 rounded-2xl flex items-center justify-center">
                            <MicrophoneIcon class="h-6 w-6" />
                        </div>
                    </div>
                    <div class="text-4xl font-black mb-1 text-gray-900">{{ mediaMentions.length }}</div>
                    <div class="text-gray-400 font-bold uppercase tracking-widest text-xs">Mentions Médias</div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
                <!-- Partnerships Section -->
                <section>
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-2xl font-black text-gray-900 tracking-tight">Derniers Partenariats</h2>
                        <Link :href="route('ecosystem.partnerships')" class="text-indigo-600 font-black text-sm flex items-center gap-2 hover:gap-3 transition-all">
                            Voir tout <ArrowRightIcon class="h-4 w-4" />
                        </Link>
                    </div>
                    <div class="space-y-4">
                        <div v-for="partner in partnerships" :key="partner.id" class="flex items-center gap-4 p-4 bg-white rounded-2xl border border-gray-50 shadow-sm">
                            <div class="h-12 w-12 bg-gray-50 rounded-xl flex items-center justify-center text-gray-400 font-black text-xs">
                                {{ partner.nom.substring(0,2).toUpperCase() }}
                            </div>
                            <div class="flex-1">
                                <h3 class="font-black text-gray-900">{{ partner.nom }}</h3>
                                <div class="flex items-center justify-between">
                                    <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">{{ partner.type }}</p>
                                    <p class="text-[10px] text-gray-400 font-black">{{ formatDate(partner.date_signature) }}</p>
                                </div>
                            </div>
                        </div>
                        <div v-if="partnerships.length === 0" class="p-8 border-2 border-dashed border-gray-100 rounded-[2rem] text-center text-gray-400 font-bold">
                            Aucun partenariat enregistré.
                        </div>
                    </div>
                </section>

                <!-- Events Section -->
                <section>
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-2xl font-black text-gray-900 tracking-tight">Événements Récents</h2>
                        <Link :href="route('ecosystem.events')" class="text-indigo-600 font-black text-sm flex items-center gap-2 hover:gap-3 transition-all">
                            Voir tout <ArrowRightIcon class="h-4 w-4" />
                        </Link>
                    </div>
                    <div class="space-y-4">
                        <div v-for="event in events" :key="event.id" class="p-4 bg-white rounded-2xl border border-gray-50 shadow-sm">
                            <h3 class="font-black text-gray-900 mb-1">{{ event.titre }}</h3>
                            <div class="flex items-center gap-2 text-xs text-gray-400 font-bold uppercase tracking-widest">
                                <CalendarIcon class="h-4 w-4" />
                                {{ formatDate(event.date) }}
                                <span v-if="event.heure_debut" class="ml-1 opacity-60 lowercase">
                                    à {{ formatTime(event.heure_debut) }}
                                </span>
                            </div>
                        </div>
                        <div v-if="events.length === 0" class="p-8 border-2 border-dashed border-gray-100 rounded-[2rem] text-center text-gray-400 font-bold">
                            Aucun événement enregistré.
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
