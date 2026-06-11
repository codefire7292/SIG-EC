<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link } from '@inertiajs/vue3'
import { 
    DocumentDuplicateIcon, 
    ClockIcon, 
    CheckBadgeIcon,
    ChartPieIcon,
    ArrowRightIcon,
    DocumentTextIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
    stats: Object,
    recent: Array,
})

const formatType = (type) => {
    const dictionary = {
        residence: 'Résidence',
        coutume: 'Coutume',
        indigence: 'Indigence',
        individualite: 'Individualité',
        vie_collective: 'Vie Collective',
        vie_individuel: 'Vie Individuelle',
        non_inscrit_naissance: 'Non Inscrit',
        acte_non_inexistant: 'Non Inexistant',
    }
    return dictionary[type] || type
}
</script>

<template>
    <Head title="Tableau de Bord" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-black text-2xl text-gray-900 tracking-tighter italic">Console de Gestion SIG-EC</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Stats Grid -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-12">
                    <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-gray-100 relative overflow-hidden group">
                        <div class="absolute top-0 right-0 p-4 opacity-5 group-hover:scale-110 transition-transform">
                            <DocumentDuplicateIcon class="h-24 w-24" />
                        </div>
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Total Registres</p>
                        <h3 class="text-5xl font-black text-gray-900 tracking-tighter">{{ stats.total }}</h3>
                        <p class="text-xs text-gray-400 mt-4 font-bold">Actes enregistrés cette année</p>
                    </div>

                    <div class="bg-blue-600 p-8 rounded-[2.5rem] shadow-xl shadow-blue-100 relative overflow-hidden group text-white">
                        <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:scale-110 transition-transform">
                            <ClockIcon class="h-24 w-24" />
                        </div>
                        <p class="text-[10px] font-black text-blue-200 uppercase tracking-widest mb-2">En Attente</p>
                        <h3 class="text-5xl font-black tracking-tighter">{{ stats.pending }}</h3>
                        <p class="text-xs text-blue-100 mt-4 font-bold">Actions de validation requises</p>
                    </div>

                    <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-gray-100 relative overflow-hidden group">
                        <div class="absolute top-0 right-0 p-4 opacity-5 group-hover:scale-110 transition-transform">
                            <CheckBadgeIcon class="h-24 w-24" />
                        </div>
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Actes Validés</p>
                        <h3 class="text-5xl font-black text-green-600 tracking-tighter">{{ stats.validated }}</h3>
                        <p class="text-xs text-gray-400 mt-4 font-bold">Délivrés avec succès</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">
                    <!-- Recent Activities -->
                    <div class="lg:col-span-2 space-y-8">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-black text-gray-900 tracking-tight flex items-center gap-2">
                                <DocumentTextIcon class="h-5 w-5 text-blue-600" />
                                Dernières Inscriptions
                            </h3>
                            <Link :href="route('civil-certificates.index')" class="text-xs font-black text-blue-600 uppercase tracking-widest hover:underline">Voir tout</Link>
                        </div>

                        <div class="bg-white rounded-[2.5rem] border border-gray-100 overflow-hidden shadow-sm">
                            <div v-if="recent.length > 0" class="divide-y divide-gray-50">
                                <Link 
                                    v-for="cert in recent" 
                                    :key="cert.id"
                                    :href="route('civil-certificates.show', cert.id)"
                                    class="p-6 flex items-center justify-between hover:bg-gray-50 transition-colors group"
                                >
                                    <div class="flex items-center gap-4">
                                        <div class="h-12 w-12 rounded-2xl bg-gray-50 flex items-center justify-center text-gray-400 group-hover:bg-blue-50 group-hover:text-blue-600 transition-colors">
                                            <DocumentTextIcon class="h-6 w-6" />
                                        </div>
                                        <div>
                                            <p class="text-sm font-black text-gray-900">{{ cert.applicant_first_name }} {{ cert.applicant_last_name }}</p>
                                            <div class="flex items-center gap-2 mt-0.5">
                                                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-tight">{{ formatType(cert.type) }}</span>
                                                <span class="text-[10px] text-gray-300">•</span>
                                                <span class="text-[10px] text-gray-400 font-mono">{{ cert.reference_number }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-4">
                                        <span 
                                            :class="{
                                                'px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-widest': true,
                                                'bg-yellow-50 text-yellow-600': cert.status === 'pending',
                                                'bg-green-50 text-green-600': cert.status === 'validated'
                                            }"
                                        >
                                            {{ cert.status === 'pending' ? 'Attente' : 'Validé' }}
                                        </span>
                                        <ArrowRightIcon class="h-4 w-4 text-gray-300 group-hover:text-blue-600 transition-colors" />
                                    </div>
                                </Link>
                            </div>
                            <div v-else class="p-12 text-center">
                                <p class="text-sm text-gray-400 font-medium">Aucune activité récente pour le moment.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Distrbution -->
                    <div class="space-y-8">
                        <h3 class="text-lg font-black text-gray-900 tracking-tight flex items-center gap-2">
                            <ChartPieIcon class="h-5 w-5 text-orange-500" />
                            Répartition des Actes
                        </h3>

                        <div class="bg-white p-8 rounded-[2.5rem] border border-gray-100 shadow-sm space-y-6">
                            <div v-for="(count, type) in stats.by_type" :key="type" class="space-y-2">
                                <div class="flex justify-between items-center text-xs font-bold">
                                    <span class="text-gray-500 uppercase tracking-widest">{{ formatType(type) }}</span>
                                    <span class="text-gray-900">{{ count }}</span>
                                </div>
                                <div class="h-2 w-full bg-gray-100 rounded-full overflow-hidden">
                                    <div 
                                        class="h-full bg-blue-600 rounded-full transition-all duration-1000"
                                        :style="{ width: (count / stats.total * 100) + '%' }"
                                    ></div>
                                </div>
                            </div>
                            
                            <div v-if="Object.keys(stats.by_type).length === 0" class="text-center py-6">
                                <p class="text-xs text-gray-400 font-medium">Pas encore de données de répartition.</p>
                            </div>
                        </div>
                        
                        <div class="p-6 bg-blue-50 rounded-3xl border border-blue-100">
                             <p class="text-[10px] font-black text-blue-900 uppercase tracking-[0.2em] mb-2">Note de Service</p>
                             <p class="text-xs text-blue-700 leading-relaxed font-medium">
                                 Pensez à vérifier régulièrement les certificats en attente. Une validation rapide améliore la satisfaction des citoyens.
                             </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
