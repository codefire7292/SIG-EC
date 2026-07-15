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
            <!-- Hero Banner Enterprise -->
            <div class="relative bg-white pt-8 pb-10 overflow-hidden border-b border-gray-200 shadow-[0_2px_10px_rgb(0,0,0,0.02)]">
                <!-- Motif décoratif très subtil -->
                <div class="absolute inset-0 opacity-[0.02]" style="background-image: radial-gradient(circle at 1px 1px, black 1px, transparent 0); background-size: 24px 24px;"></div>
                
                <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
                        <div>
                            <div class="flex items-center gap-3 mb-4">
                                <div class="h-10 w-10 bg-white rounded-xl flex items-center justify-center border border-gray-200 shadow-sm">
                                    <img src="/images/logo.png" alt="Logo" class="h-6 w-6 object-contain" />
                                </div>
                                <span class="px-2.5 py-1 bg-[#F2F9EE] text-[#1E690F] rounded-md text-[10px] font-bold uppercase tracking-widest border border-[#D9EDD0]">
                                    Console d'État Civil
                                </span>
                            </div>
                            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 tracking-tight leading-tight">
                                Bonjour, {{ $page.props.auth.user.name.split(' ')[0] }}
                            </h1>
                            <p class="text-gray-500 font-medium mt-1.5 max-w-xl text-sm">
                                Bienvenue sur l'espace d'administration de la Mairie de Enampore. Voici un aperçu de l'activité en cours.
                            </p>
                        </div>
                        
                        <div class="flex flex-col sm:flex-row items-center gap-3">
                            <Link 
                                :href="route('civil-certificates.index')"
                                class="flex items-center justify-center gap-2 px-4 py-2.5 bg-white text-gray-700 hover:bg-gray-50 hover:text-gray-900 text-sm font-semibold rounded-lg border border-gray-300 shadow-sm transition-all duration-200 w-full sm:w-auto"
                            >
                                Explorer les Registres
                            </Link>
                            <Link 
                                v-if="$page.props.auth.user.permissions?.includes('create-drafts')"
                                :href="route('civil-certificates.create')"
                                class="flex items-center justify-center gap-2 px-5 py-2.5 bg-[#1E690F] text-white hover:bg-[#154B0B] text-sm font-semibold rounded-lg shadow-sm transition-all duration-200 w-full sm:w-auto"
                            >
                                <PlusCircleIcon class="h-5 w-5" />
                                Nouvel Acte
                            </Link>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contenu Principal -->
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-8">
                
                <!-- KPI Principaux -->
                <div class="grid grid-cols-1 md:grid-cols-12 gap-6 mb-8">
                    <!-- Grand Bloc : Attente -->
                    <div class="md:col-span-4 bg-white rounded-2xl p-6 shadow-sm border border-gray-200 hover:border-gray-300 transition-all duration-200 flex flex-col justify-between min-h-[180px]">
                        <div>
                            <div class="flex items-center justify-between mb-4">
                                <div class="h-10 w-10 rounded-lg bg-amber-50 border border-amber-100 flex items-center justify-center">
                                    <ClockIcon class="h-5 w-5 text-amber-600" />
                                </div>
                                <span class="px-2 py-1 bg-amber-50 text-amber-700 text-[10px] font-bold rounded-md border border-amber-200 uppercase tracking-widest">Priorité</span>
                            </div>
                            <h3 class="text-[11px] font-bold text-gray-500 uppercase tracking-widest mb-1">Actions Requises</h3>
                            <div class="flex items-baseline gap-2">
                                <span class="text-4xl font-bold text-gray-900 tracking-tight">{{ stats.pending }}</span>
                                <span class="text-xs font-semibold text-gray-500">en attente</span>
                            </div>
                        </div>
                    </div>

                    <!-- Grille 2x2 KPIs -->
                    <div class="md:col-span-8 grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-200 hover:border-gray-300 transition-colors duration-200 flex flex-col justify-between min-h-[130px]">
                            <div class="flex justify-between items-start mb-3">
                                <div class="h-8 w-8 rounded-lg bg-gray-50 flex items-center justify-center border border-gray-100">
                                    <DocumentDuplicateIcon class="h-4 w-4 text-gray-600" />
                                </div>
                            </div>
                            <div>
                                <h3 class="text-2xl font-bold text-gray-900 tracking-tight">{{ stats.total }}</h3>
                                <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest mt-1">Total Demandes</p>
                            </div>
                        </div>

                        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-200 hover:border-gray-300 transition-colors duration-200 flex flex-col justify-between min-h-[130px]">
                            <div class="flex justify-between items-start mb-3">
                                <div class="h-8 w-8 rounded-lg bg-green-50 flex items-center justify-center border border-green-100">
                                    <CheckBadgeIcon class="h-4 w-4 text-green-600" />
                                </div>
                            </div>
                            <div>
                                <h3 class="text-2xl font-bold text-gray-900 tracking-tight">{{ stats.validated }}</h3>
                                <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest mt-1">Demandes Validées</p>
                            </div>
                        </div>
                        
                        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-200 hover:border-gray-300 transition-colors duration-200 flex flex-col justify-between min-h-[130px]">
                            <div class="flex justify-between items-start mb-3">
                                <div class="h-8 w-8 rounded-lg bg-blue-50 flex items-center justify-center border border-blue-100">
                                    <DocumentTextIcon class="h-4 w-4 text-blue-600" />
                                </div>
                            </div>
                            <div>
                                <h3 class="text-2xl font-bold text-gray-900 tracking-tight">{{ stats.births_count }}</h3>
                                <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest mt-1">Naissances Enregistrées</p>
                            </div>
                        </div>

                        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-200 hover:border-gray-300 transition-colors duration-200 flex flex-col justify-between min-h-[130px]">
                            <div class="flex justify-between items-start mb-3">
                                <div class="h-8 w-8 rounded-lg bg-purple-50 flex items-center justify-center border border-purple-100">
                                    <DocumentTextIcon class="h-4 w-4 text-purple-600" />
                                </div>
                            </div>
                            <div class="flex items-baseline gap-4">
                                <div>
                                    <h3 class="text-2xl font-bold text-gray-900 tracking-tight">{{ stats.marriages_count }}<span class="text-xs font-semibold text-gray-400 ml-1">M</span></h3>
                                </div>
                                <div>
                                    <h3 class="text-2xl font-bold text-gray-900 tracking-tight">{{ stats.deaths_count }}<span class="text-xs font-semibold text-gray-400 ml-1">D</span></h3>
                                </div>
                            </div>
                            <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest mt-1">Mariages & Décès</p>
                        </div>
                    </div>
                </div>

                <!-- Section Administration & Suivi des Actes -->
                <div class="mb-8">
                    <h3 class="text-[11px] font-bold text-gray-500 uppercase tracking-widest mb-4">Indicateurs de Performance</h3>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-200">
                            <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Registres Ouverts</p>
                            <h4 class="text-2xl font-bold text-gray-900 tracking-tight mt-1">{{ stats.registries_open }}</h4>
                            <p class="text-[10px] text-gray-500 font-medium mt-1.5 flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> {{ stats.acts_signe }} actes signés</p>
                        </div>
                        <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-200">
                            <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Registres Clos</p>
                            <h4 class="text-2xl font-bold text-gray-900 tracking-tight mt-1">{{ stats.registries_closed }}</h4>
                            <p class="text-[10px] text-gray-500 font-medium mt-1.5 flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> {{ stats.acts_a_corriger }} à corriger</p>
                        </div>
                        <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-200">
                            <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Centres Actifs</p>
                            <h4 class="text-2xl font-bold text-gray-900 tracking-tight mt-1">{{ stats.centers_count }}</h4>
                            <p class="text-[10px] text-gray-500 font-medium mt-1.5 flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span> {{ stats.acts_valide }} à signer</p>
                        </div>
                        <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-200">
                            <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Utilisateurs</p>
                            <h4 class="text-2xl font-bold text-gray-900 tracking-tight mt-1">{{ stats.users_count }}</h4>
                            <p class="text-[10px] text-gray-500 font-medium mt-1.5 flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span> {{ stats.acts_draft }} brouillons</p>
                        </div>
                    </div>
                </div>

                <!-- Section Graphiques & Activités -->
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                    
                    <!-- Colonne de Gauche : Graphiques -->
                    <div class="lg:col-span-7 space-y-6">
                        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-200">
                            <div class="mb-6">
                                <h3 class="text-sm font-bold text-gray-900 tracking-tight">Vue d'ensemble des Actes</h3>
                                <p class="text-xs text-gray-500 font-medium mt-0.5">Volume d'enregistrements par type de document civil</p>
                            </div>
                            <div class="h-64">
                                <Bar :data="barData" :options="barOptions" />
                            </div>
                        </div>

                        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-200">
                            <h3 class="text-sm font-bold text-gray-900 tracking-tight mb-5">
                                Répartition des Types de Certificats
                            </h3>

                            <div class="space-y-4">
                                <div 
                                    v-for="(count, type, index) in stats.by_type" 
                                    :key="type" 
                                    class="space-y-1.5"
                                >
                                    <div class="flex justify-between items-center text-xs font-semibold">
                                        <span class="text-gray-600">{{ formatType(type) }}</span>
                                        <span class="text-gray-900 font-bold">{{ count }}</span>
                                    </div>
                                    <div class="h-1.5 w-full bg-gray-100 rounded-full overflow-hidden">
                                        <div 
                                            class="h-full rounded-full"
                                            :class="typeColors[index % typeColors.length]"
                                            :style="{ width: (stats.total > 0 ? (count / stats.total * 100) : 0) + '%' }"
                                        ></div>
                                    </div>
                                </div>
                                
                                <div v-if="Object.keys(stats.by_type).length === 0" class="text-center py-6">
                                    <p class="text-xs text-gray-500 font-medium">Pas de données de répartition.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Colonne de Droite : Feed d'Activité -->
                    <div class="lg:col-span-5">
                        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 flex flex-col h-full overflow-hidden">
                            <div class="p-5 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
                                <div>
                                    <h3 class="text-sm font-bold text-gray-900 tracking-tight">Activité Récente</h3>
                                </div>
                                <Link :href="route('civil-certificates.index')" class="text-[11px] font-semibold text-gray-600 hover:text-gray-900 transition-colors">
                                    Voir tout
                                </Link>
                            </div>
                            
                            <div class="flex-1 p-0">
                                <ul v-if="recent.length > 0" class="divide-y divide-gray-100">
                                    <li v-for="cert in recent" :key="cert.id">
                                        <Link 
                                            :href="route('civil-certificates.show', cert.id)"
                                            class="flex items-center gap-4 px-5 py-4 hover:bg-gray-50 transition-colors group block"
                                        >
                                            <div class="flex-1 min-w-0">
                                                <div class="flex items-center justify-between mb-1">
                                                    <p class="text-sm font-bold text-gray-900 truncate">{{ cert.applicant_first_name }} {{ cert.applicant_last_name }}</p>
                                                    <span 
                                                        class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider"
                                                        :class="cert.status === 'validated' ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700'"
                                                    >
                                                        {{ cert.status === 'pending' ? 'Attente' : 'Validé' }}
                                                    </span>
                                                </div>
                                                <div class="flex items-center justify-between text-[11px]">
                                                    <span class="font-medium text-gray-500">{{ formatType(cert.type) }}</span>
                                                    <span class="font-mono text-gray-400">{{ cert.reference_number }}</span>
                                                </div>
                                            </div>
                                        </Link>
                                    </li>
                                </ul>
                                <div v-else class="p-12 text-center">
                                    <DocumentTextIcon class="h-8 w-8 text-gray-300 mx-auto mb-3" />
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
