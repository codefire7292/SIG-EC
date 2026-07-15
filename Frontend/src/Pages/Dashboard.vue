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
        <!-- Retirer le header natif pour intégrer un Hero personnalisé -->
        <template #header>
            <div class="hidden"></div>
        </template>

        <div class="min-h-screen bg-[#F8FAFC] pb-12">
            <!-- Hero Banner Premium -->
            <div class="relative bg-white pt-10 pb-16 overflow-hidden border-b border-gray-100 shadow-sm" style="background: linear-gradient(135deg, #0A2903 0%, #1E690F 100%);">
                <!-- Motif décoratif -->
                <div class="absolute inset-0 opacity-10" style="background-image: radial-gradient(circle at 2px 2px, white 1px, transparent 0); background-size: 32px 32px;"></div>
                <div class="absolute top-0 right-0 -mr-20 -mt-20 w-96 h-96 rounded-full bg-white/10 blur-3xl mix-blend-overlay pointer-events-none"></div>
                <div class="absolute bottom-0 left-0 -ml-20 -mb-20 w-80 h-80 rounded-full bg-[#F0C31E]/20 blur-3xl mix-blend-overlay pointer-events-none"></div>
                
                <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
                        <div>
                            <div class="flex items-center gap-3 mb-4">
                                <div class="h-10 w-10 bg-white/10 rounded-2xl backdrop-blur-md flex items-center justify-center border border-white/20">
                                    <img src="/images/logo.png" alt="Logo" class="h-6 w-6 object-contain" />
                                </div>
                                <span class="px-3 py-1 bg-white/10 rounded-full text-[10px] font-black text-white uppercase tracking-widest border border-white/20 backdrop-blur-md">
                                    Système d'État Civil
                                </span>
                            </div>
                            <h1 class="text-3xl sm:text-4xl font-black text-white tracking-tight leading-tight">
                                Bonjour, {{ $page.props.auth.user.name.split(' ')[0] }}
                            </h1>
                            <p class="text-green-100/80 font-medium mt-2 max-w-xl text-sm sm:text-base">
                                Bienvenue sur la console de gestion de la Mairie de Enampore. Voici un aperçu de l'activité récente.
                            </p>
                        </div>
                        
                        <div class="flex flex-col sm:flex-row items-center gap-3">
                            <Link 
                                :href="route('civil-certificates.index')"
                                class="flex items-center justify-center gap-2 px-5 py-3.5 bg-white/10 text-white hover:bg-white/20 text-sm font-black rounded-2xl backdrop-blur-md border border-white/20 transition-all duration-300 w-full sm:w-auto"
                            >
                                Registres
                            </Link>
                            <Link 
                                v-if="$page.props.auth.user.permissions?.includes('create-drafts')"
                                :href="route('civil-certificates.create')"
                                class="flex items-center justify-center gap-2 px-6 py-3.5 text-[#0A2903] text-sm font-black rounded-2xl shadow-xl hover:shadow-2xl transition-all duration-300 hover:scale-105 active:scale-95 w-full sm:w-auto"
                                style="background: linear-gradient(135deg, #F0C31E 0%, #FFD700 100%);"
                            >
                                <PlusCircleIcon class="h-5 w-5" />
                                Nouvel Acte
                            </Link>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contenu Principal -->
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-8 relative z-10">
                
                <!-- Bento Box : KPI Principaux -->
                <div class="grid grid-cols-1 md:grid-cols-12 gap-6 mb-8">
                    <!-- Grand Bloc : Attente -->
                    <div class="md:col-span-4 bg-white rounded-3xl p-6 sm:p-8 shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-gray-100 hover:shadow-[0_8px_30px_rgb(0,0,0,0.08)] transition-all duration-500 group relative overflow-hidden flex flex-col justify-between min-h-[220px]">
                        <div class="absolute -right-6 -top-6 w-32 h-32 bg-red-50 rounded-full blur-3xl group-hover:bg-red-100 transition-colors duration-500 pointer-events-none"></div>
                        <div>
                            <div class="h-12 w-12 rounded-2xl bg-amber-50 border border-amber-100 flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-500">
                                <ClockIcon class="h-6 w-6 text-amber-500" />
                            </div>
                            <h3 class="text-[11px] font-black text-gray-400 uppercase tracking-widest mb-2">Actions Requises</h3>
                            <div class="flex items-baseline gap-2">
                                <span class="text-5xl font-black text-gray-900 tracking-tighter">{{ stats.pending }}</span>
                                <span class="text-sm font-bold text-amber-500">en attente</span>
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 font-medium mt-6">Certificats nécessitant une validation urgente.</p>
                    </div>

                    <!-- Grille 2x2 KPIs -->
                    <div class="md:col-span-8 grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div class="bg-white rounded-3xl p-6 shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-gray-100 hover:border-[#1E690F]/20 transition-colors duration-300 group flex flex-col justify-between min-h-[140px]">
                            <div class="flex justify-between items-start mb-4">
                                <div class="h-10 w-10 rounded-xl bg-[#F2F9EE] flex items-center justify-center group-hover:bg-[#1E690F] transition-colors duration-300">
                                    <DocumentDuplicateIcon class="h-5 w-5 text-[#1E690F] group-hover:text-white transition-colors" />
                                </div>
                            </div>
                            <div>
                                <h3 class="text-3xl font-black text-gray-900 tracking-tighter">{{ stats.total }}</h3>
                                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mt-1">Total Demandes</p>
                            </div>
                        </div>

                        <div class="bg-white rounded-3xl p-6 shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-gray-100 hover:border-green-500/20 transition-colors duration-300 group flex flex-col justify-between min-h-[140px]">
                            <div class="flex justify-between items-start mb-4">
                                <div class="h-10 w-10 rounded-xl bg-green-50 flex items-center justify-center group-hover:bg-green-500 transition-colors duration-300">
                                    <CheckBadgeIcon class="h-5 w-5 text-green-500 group-hover:text-white transition-colors" />
                                </div>
                            </div>
                            <div>
                                <h3 class="text-3xl font-black text-gray-900 tracking-tighter">{{ stats.validated }}</h3>
                                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mt-1">Demandes Validées</p>
                            </div>
                        </div>
                        
                        <div class="bg-white rounded-3xl p-6 shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-gray-100 group flex flex-col justify-between relative overflow-hidden min-h-[140px]">
                            <div class="absolute bottom-0 right-0 p-4 opacity-[0.03] group-hover:scale-110 transition-transform duration-500 pointer-events-none">
                                <DocumentTextIcon class="h-24 w-24" />
                            </div>
                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3 relative z-10">Naissances Enregistrées</p>
                            <h3 class="text-3xl font-black text-[#1E690F] tracking-tighter relative z-10">{{ stats.births_count }}</h3>
                        </div>

                        <div class="bg-white rounded-3xl p-6 shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-gray-100 group flex flex-col justify-between relative overflow-hidden min-h-[140px]">
                            <div class="absolute bottom-0 right-0 p-4 opacity-[0.03] group-hover:scale-110 transition-transform duration-500 pointer-events-none">
                                <DocumentTextIcon class="h-24 w-24" />
                            </div>
                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3 relative z-10">Mariages & Décès</p>
                            <div class="flex items-baseline gap-3 relative z-10">
                                <h3 class="text-3xl font-black text-[#1E690F] tracking-tighter">{{ stats.marriages_count }}<span class="text-sm font-bold text-gray-400 tracking-normal ml-1">M</span></h3>
                                <h3 class="text-3xl font-black text-[#1E690F] tracking-tighter">{{ stats.deaths_count }}<span class="text-sm font-bold text-gray-400 tracking-normal ml-1">D</span></h3>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section Graphiques & Activités -->
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                    
                    <!-- Colonne de Gauche : Graphiques -->
                    <div class="lg:col-span-7 space-y-8">
                        <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-gray-100">
                            <div class="flex items-center justify-between mb-8">
                                <div>
                                    <h3 class="text-lg font-black text-gray-900 tracking-tight">Vue d'ensemble des Actes</h3>
                                    <p class="text-xs text-gray-500 font-medium mt-1">Volume d'enregistrements par type</p>
                                </div>
                                <div class="h-10 w-10 bg-gray-50 rounded-xl flex items-center justify-center">
                                    <ChartPieIcon class="h-5 w-5 text-gray-400" />
                                </div>
                            </div>
                            <div class="h-64">
                                <Bar :data="barData" :options="barOptions" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            <div class="bg-white rounded-3xl p-6 shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-gray-100 text-center hover:shadow-[0_8px_30px_rgb(0,0,0,0.08)] transition-shadow">
                                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Registres Ouverts</p>
                                <h4 class="text-4xl font-black text-gray-900 tracking-tighter">{{ stats.registries_open }}</h4>
                            </div>
                            <div class="bg-white rounded-3xl p-6 shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-gray-100 text-center hover:shadow-[0_8px_30px_rgb(0,0,0,0.08)] transition-shadow">
                                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Utilisateurs</p>
                                <h4 class="text-4xl font-black text-gray-900 tracking-tighter">{{ stats.users_count }}</h4>
                            </div>
                        </div>
                    </div>

                    <!-- Colonne de Droite : Feed d'Activité -->
                    <div class="lg:col-span-5">
                        <div class="bg-white rounded-3xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-gray-100 flex flex-col h-full overflow-hidden">
                            <div class="p-6 sm:p-8 border-b border-gray-50 flex items-center justify-between">
                                <div>
                                    <h3 class="text-lg font-black text-gray-900 tracking-tight">Activité Récente</h3>
                                    <p class="text-xs text-gray-500 font-medium mt-1">Dernières demandes soumises</p>
                                </div>
                                <Link :href="route('civil-certificates.index')" class="text-[10px] font-black text-[#1E690F] uppercase tracking-widest hover:bg-[#1E690F] hover:text-white transition-colors px-3 py-1.5 bg-[#F2F9EE] rounded-lg">
                                    Voir tout
                                </Link>
                            </div>
                            
                            <div class="flex-1 p-3">
                                <div v-if="recent.length > 0" class="divide-y divide-gray-50">
                                    <Link 
                                        v-for="cert in recent" 
                                        :key="cert.id"
                                        :href="route('civil-certificates.show', cert.id)"
                                        class="flex items-center gap-4 p-4 rounded-2xl hover:bg-gray-50/80 transition-colors group"
                                    >
                                        <div class="h-12 w-12 rounded-2xl flex items-center justify-center flex-shrink-0 transition-colors duration-300"
                                            :class="cert.status === 'validated' ? 'bg-green-50 text-green-600 group-hover:bg-green-500 group-hover:text-white' : 'bg-amber-50 text-amber-500 group-hover:bg-amber-500 group-hover:text-white'"
                                        >
                                            <DocumentTextIcon class="h-5 w-5" />
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-black text-gray-900 truncate">{{ cert.applicant_first_name }} {{ cert.applicant_last_name }}</p>
                                            <div class="flex items-center gap-2 mt-1">
                                                <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">{{ formatType(cert.type) }}</span>
                                            </div>
                                        </div>
                                        <div class="flex flex-col items-end gap-2">
                                            <span 
                                                class="px-2.5 py-1 rounded-full text-[9px] font-black uppercase tracking-widest border"
                                                :class="cert.status === 'validated' ? 'bg-green-50 text-green-600 border-green-100' : 'bg-amber-50 text-amber-600 border-amber-100'"
                                            >
                                                {{ cert.status === 'pending' ? 'Attente' : 'Validé' }}
                                            </span>
                                            <span class="text-[10px] font-mono text-gray-400 group-hover:text-gray-900 transition-colors">{{ cert.reference_number }}</span>
                                        </div>
                                    </Link>
                                </div>
                                <div v-else class="p-12 text-center">
                                    <div class="h-16 w-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4">
                                        <DocumentTextIcon class="h-8 w-8 text-gray-300" />
                                    </div>
                                    <p class="text-sm text-gray-500 font-medium">Aucune activité récente.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
