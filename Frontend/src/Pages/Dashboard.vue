<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link } from '@inertiajs/vue3'
import { 
    DocumentDuplicateIcon, 
    ClockIcon, 
    CheckBadgeIcon,
    ChartPieIcon,
    ArrowRightIcon,
    DocumentTextIcon,
    PlusCircleIcon
} from '@heroicons/vue/24/outline'

import { Doughnut, Bar } from 'vue-chartjs'
import {
  Chart as ChartJS,
  Title,
  Tooltip,
  Legend,
  ArcElement,
  BarElement,
  CategoryScale,
  LinearScale
} from 'chart.js'

ChartJS.register(Title, Tooltip, Legend, ArcElement, BarElement, CategoryScale, LinearScale)

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

// Couleur de barre selon le type d'acte
const typeColors = [
    'bg-[#1E690F]', // Vert Enampore principal
    'bg-[#3D9426]', // Vert plus clair
    'bg-[#F0C31E]', // Or Enampore
    'bg-[#D9A100]', // Or plus foncé
    'bg-[#185709]', // Vert foncé
]

// Configuration des diagrammes
const doughnutData = {
  labels: Object.keys(props.stats.by_type).map(type => formatType(type)),
  datasets: [
    {
      backgroundColor: ['#1E690F', '#3D9426', '#F0C31E', '#D9A100', '#185709'],
      data: Object.values(props.stats.by_type)
    }
  ]
}

const doughnutOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      position: 'bottom',
      labels: {
        font: {
          family: 'Outfit, sans-serif',
          weight: 'bold',
          size: 10
        },
        color: '#4B5563'
      }
    }
  }
}

const barData = {
  labels: ['Naissances', 'Mariages', 'Décès'],
  datasets: [
    {
      label: 'Actes Enregistrés',
      backgroundColor: '#1E690F',
      borderRadius: 8,
      data: [props.stats.births_count, props.stats.marriages_count, props.stats.deaths_count]
    }
  ]
}

const barOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      display: false
    }
  },
  scales: {
    y: {
      beginAtZero: true,
      ticks: {
        font: {
          family: 'Outfit, sans-serif',
          weight: 'bold',
          size: 10
        }
      }
    },
    x: {
      ticks: {
        font: {
          family: 'Outfit, sans-serif',
          weight: 'bold',
          size: 10
        }
      }
    }
  }
}
</script>

<template>
    <Head title="Tableau de Bord — Mairie de Enampore" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="font-black text-lg sm:text-xl text-brand-800 tracking-tight flex items-center gap-2">
                        <img src="/images/logo.png" alt="Mairie de Enampore" class="h-6 w-6 sm:h-7 sm:w-7 object-contain hidden sm:block" />
                        <span class="leading-tight">Console de Gestion — Mairie de Enampore</span>
                    </h2>
                    <p class="text-[10px] sm:text-xs text-gray-400 font-medium mt-1">Système Intégré de Gestion de l'État Civil — Commune de Enampore</p>
                </div>
                <Link 
                    v-if="$page.props.auth.user.permissions?.includes('create-drafts')"
                    :href="route('civil-certificates.create')"
                    class="flex items-center justify-center gap-2 px-4 py-3 sm:py-2.5 text-white text-sm font-black rounded-xl shadow-brand hover:shadow-brand-lg transition-all duration-200 w-full sm:w-auto"
                    style="background: linear-gradient(135deg, #1E690F 0%, #3D9426 100%);"
                >
                    <PlusCircleIcon class="h-5 w-5" />
                    Nouvel Acte
                </Link>
            </div>
        </template>

        <div class="py-8">
            <div class="max-w-7xl mx-auto px-2 sm:px-4">
                <!-- Section Certificats -->
                <div class="mb-10">
                    <h3 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-4">Certificats & Demandes Citoyennes</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- Total Certificats -->
                        <div class="bg-white p-7 rounded-2xl shadow-sm border border-brand-50 relative overflow-hidden group hover:shadow-brand transition-shadow duration-300">
                            <div class="absolute top-0 right-0 p-4 opacity-5 group-hover:scale-110 transition-transform duration-300">
                                <DocumentDuplicateIcon class="h-24 w-24 text-brand-500" />
                            </div>
                            <div class="h-10 w-10 rounded-xl bg-brand-50 border border-brand-100 flex items-center justify-center mb-4">
                                <DocumentDuplicateIcon class="h-5 w-5 text-brand-500" />
                            </div>
                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Total Certificats</p>
                            <h3 class="text-4xl font-black text-brand-800 tracking-tighter">{{ stats.total }}</h3>
                            <p class="text-xs text-gray-400 mt-3 font-semibold">Demandes enregistrées au total</p>
                        </div>

                        <!-- En Attente -->
                        <div class="p-7 rounded-2xl shadow-brand-lg relative overflow-hidden group text-white" 
                             style="background: linear-gradient(135deg, #0A2903 0%, #1E690F 100%);">
                            <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:scale-110 transition-transform duration-300">
                                <ClockIcon class="h-24 w-24" />
                            </div>
                            <div class="h-10 w-10 rounded-xl bg-white/10 border border-white/20 flex items-center justify-center mb-4">
                                <ClockIcon class="h-5 w-5 text-green-100" />
                            </div>
                            <p class="text-[10px] font-black text-green-200/80 uppercase tracking-widest mb-1">En Attente</p>
                            <h3 class="text-4xl font-black tracking-tighter">{{ stats.pending }}</h3>
                            <p class="text-xs text-green-100/70 mt-3 font-semibold">Actions de validation requises</p>
                        </div>

                        <!-- Actes Validés -->
                        <div class="bg-white p-7 rounded-2xl shadow-sm border border-green-50 relative overflow-hidden group hover:shadow-md transition-shadow duration-300">
                            <div class="absolute top-0 right-0 p-4 opacity-5 group-hover:scale-110 transition-transform duration-300">
                                <CheckBadgeIcon class="h-24 w-24 text-green-500" />
                            </div>
                            <div class="h-10 w-10 rounded-xl bg-green-50 border border-green-100 flex items-center justify-center mb-4">
                                <CheckBadgeIcon class="h-5 w-5 text-green-500" />
                            </div>
                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Certificats Validés</p>
                            <h3 class="text-4xl font-black text-green-600 tracking-tighter">{{ stats.validated }}</h3>
                            <p class="text-xs text-gray-400 mt-3 font-semibold">Délivrés avec succès</p>
                        </div>
                    </div>
                </div>

                <!-- Section Actes Civils -->
                <div class="mb-10">
                    <h3 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-4">Registres d'État Civil (Naissances, Mariages, Décès)</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- Naissances -->
                        <div class="bg-white p-7 rounded-2xl shadow-sm border border-brand-50 relative overflow-hidden group hover:shadow-brand transition-shadow duration-300">
                            <div class="absolute top-0 right-0 p-4 opacity-5 group-hover:scale-110 transition-transform duration-300">
                                <DocumentTextIcon class="h-24 w-24 text-brand-500" />
                            </div>
                            <div class="h-10 w-10 rounded-xl bg-brand-50 border border-brand-100 flex items-center justify-center mb-4">
                                <span class="font-black text-sm text-[#1E690F]">N</span>
                            </div>
                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Naissances Enregistrées</p>
                            <h3 class="text-4xl font-black text-brand-800 tracking-tighter">{{ stats.births_count }}</h3>
                            <p class="text-xs text-gray-400 mt-3 font-semibold">Déclarations de naissance</p>
                        </div>

                        <!-- Mariages -->
                        <div class="bg-white p-7 rounded-2xl shadow-sm border border-brand-50 relative overflow-hidden group hover:shadow-brand transition-shadow duration-300">
                            <div class="absolute top-0 right-0 p-4 opacity-5 group-hover:scale-110 transition-transform duration-300">
                                <DocumentTextIcon class="h-24 w-24 text-brand-500" />
                            </div>
                            <div class="h-10 w-10 rounded-xl bg-brand-50 border border-brand-100 flex items-center justify-center mb-4">
                                <span class="font-black text-sm text-[#1E690F]">M</span>
                            </div>
                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Mariages Enregistrés</p>
                            <h3 class="text-4xl font-black text-brand-800 tracking-tighter">{{ stats.marriages_count }}</h3>
                            <p class="text-xs text-gray-400 mt-3 font-semibold">Unions célébrées</p>
                        </div>

                        <!-- Décès -->
                        <div class="bg-white p-7 rounded-2xl shadow-sm border border-brand-50 relative overflow-hidden group hover:shadow-brand transition-shadow duration-300">
                            <div class="absolute top-0 right-0 p-4 opacity-5 group-hover:scale-110 transition-transform duration-300">
                                <DocumentTextIcon class="h-24 w-24 text-brand-500" />
                            </div>
                            <div class="h-10 w-10 rounded-xl bg-brand-50 border border-brand-100 flex items-center justify-center mb-4">
                                <span class="font-black text-sm text-[#1E690F]">D</span>
                            </div>
                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Décès Enregistrés</p>
                            <h3 class="text-4xl font-black text-brand-800 tracking-tighter">{{ stats.deaths_count }}</h3>
                            <p class="text-xs text-gray-400 mt-3 font-semibold">Déclarations de décès</p>
                        </div>
                    </div>
                </div>

                <!-- Section Administration & Suivi des Actes -->
                <div class="mb-10">
                    <h3 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-4">Indicateurs de Performance et Administration</h3>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                        <!-- Registres Ouverts -->
                        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                            <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest">Registres Ouverts</p>
                            <h4 class="text-2xl font-black text-brand-800 mt-1">{{ stats.registries_open }}</h4>
                            <p class="text-[8px] text-green-600 font-bold mt-1 uppercase tracking-tight">{{ stats.acts_signe }} actes signés au total</p>
                        </div>
                        <!-- Registres Fermés -->
                        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                            <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest">Registres Clos</p>
                            <h4 class="text-2xl font-black text-gray-600 mt-1">{{ stats.registries_closed }}</h4>
                            <p class="text-[8px] text-amber-600 font-bold mt-1 uppercase tracking-tight">{{ stats.acts_a_corriger }} en attente de correction</p>
                        </div>
                        <!-- Centres -->
                        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                            <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest">Centres Actifs</p>
                            <h4 class="text-2xl font-black text-[#1E690F] mt-1">{{ stats.centers_count }}</h4>
                            <p class="text-[8px] text-brand-500 font-bold mt-1 uppercase tracking-tight">{{ stats.acts_valide }} validés en attente de signature</p>
                        </div>
                        <!-- Utilisateurs -->
                        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                            <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest">Utilisateurs</p>
                            <h4 class="text-2xl font-black text-brand-800 mt-1">{{ stats.users_count }}</h4>
                            <p class="text-[8px] text-red-500 font-bold mt-1 uppercase tracking-tight">{{ stats.acts_draft }} brouillons non-soumis</p>
                        </div>
                    </div>
                </div>

                <!-- Section Graphiques & Analyses -->
                <div class="mb-10">
                    <h3 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-4">Analyses & Tendances</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <!-- Volume d'Actes Civils -->
                        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm space-y-4">
                            <h3 class="text-sm font-black text-brand-800 tracking-tight flex items-center gap-2">
                                <ChartPieIcon class="h-5 w-5 text-[#1E690F]" />
                                Volume Actes Civils Enregistrés (Naissances, Mariages, Décès)
                            </h3>
                            <div class="h-64 relative">
                                <Bar :data="barData" :options="barOptions" />
                            </div>
                        </div>

                        <!-- Répartition des Demandes de Certificats -->
                        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm space-y-4">
                            <h3 class="text-sm font-black text-brand-800 tracking-tight flex items-center gap-2">
                                <ChartPieIcon class="h-5 w-5 text-[#1E690F]" />
                                Répartition des Demandes de Certificats
                            </h3>
                            <div class="h-64 relative">
                                <Doughnut :data="doughnutData" :options="doughnutOptions" />
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Dernières inscriptions -->
                    <div class="lg:col-span-2 space-y-5">
                        <div class="flex items-center justify-between">
                            <h3 class="text-base font-black text-brand-800 tracking-tight flex items-center gap-2">
                                <DocumentTextIcon class="h-5 w-5 text-brand-500" />
                                Dernières Inscriptions
                            </h3>
                            <Link :href="route('civil-certificates.index')" class="text-xs font-black text-brand-500 uppercase tracking-widest hover:text-brand-700 hover:underline transition-colors">
                                Voir tout →
                            </Link>
                        </div>

                        <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden shadow-sm">
                            <div v-if="recent.length > 0" class="divide-y divide-gray-50">
                                <Link 
                                    v-for="cert in recent" 
                                    :key="cert.id"
                                    :href="route('civil-certificates.show', cert.id)"
                                    class="p-5 flex items-center justify-between hover:bg-brand-50/50 transition-colors group"
                                >
                                    <div class="flex items-center gap-4">
                                        <div class="h-10 w-10 rounded-xl bg-brand-50 flex items-center justify-center text-brand-400 group-hover:bg-brand-500 group-hover:text-white transition-all duration-200 flex-shrink-0">
                                            <DocumentTextIcon class="h-5 w-5" />
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
                                    <div class="flex items-center gap-3">
                                        <span 
                                            :class="{
                                                'px-2.5 py-1 rounded-full text-[9px] font-black uppercase tracking-widest': true,
                                                'bg-amber-50 text-amber-600 border border-amber-100': cert.status === 'pending',
                                                'bg-green-50 text-green-600 border border-green-100': cert.status === 'validated'
                                            }"
                                        >
                                            {{ cert.status === 'pending' ? 'Attente' : 'Validé' }}
                                        </span>
                                        <ArrowRightIcon class="h-4 w-4 text-gray-300 group-hover:text-brand-500 transition-colors" />
                                    </div>
                                </Link>
                            </div>
                            <div v-else class="p-12 text-center">
                                <DocumentTextIcon class="h-12 w-12 text-gray-200 mx-auto mb-3" />
                                <p class="text-sm text-gray-400 font-medium">Aucune activité récente pour le moment.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Répartition des actes -->
                    <div class="space-y-5">
                        <h3 class="text-base font-black text-brand-800 tracking-tight flex items-center gap-2">
                            <ChartPieIcon class="h-5 w-5 text-brand-500" />
                            Répartition des Actes
                        </h3>

                        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm space-y-5">
                            <div 
                                v-for="(count, type, index) in stats.by_type" 
                                :key="type" 
                                class="space-y-1.5"
                            >
                                <div class="flex justify-between items-center text-xs font-bold">
                                    <span class="text-gray-600 uppercase tracking-wider text-[10px]">{{ formatType(type) }}</span>
                                    <span class="text-brand-800 font-black">{{ count }}</span>
                                </div>
                                <div class="h-1.5 w-full bg-gray-100 rounded-full overflow-hidden">
                                    <div 
                                        class="h-full rounded-full transition-all duration-1000"
                                        :class="typeColors[index % typeColors.length]"
                                        :style="{ width: (count / stats.total * 100) + '%' }"
                                    ></div>
                                </div>
                            </div>
                            
                            <div v-if="Object.keys(stats.by_type).length === 0" class="text-center py-6">
                                <p class="text-xs text-gray-400 font-medium">Pas encore de données de répartition.</p>
                            </div>
                        </div>
                        
                        <!-- Note de service -->
                        <div class="p-5 rounded-2xl border" style="background: linear-gradient(135deg, #F2F9EE 0%, #D9EDD0 100%); border-color: #B3D9A0;">
                            <div class="flex items-center gap-2 mb-2">
                                <div class="h-2 w-2 rounded-full" style="background: #1E690F;"></div>
                                <p class="text-[10px] font-black uppercase tracking-[0.2em]" style="color: #1E690F;">Note de Service</p>
                            </div>
                            <p class="text-xs leading-relaxed font-medium" style="color: #185709;">
                                Pensez à vérifier régulièrement les certificats en attente. Une validation rapide améliore la satisfaction des citoyens.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
