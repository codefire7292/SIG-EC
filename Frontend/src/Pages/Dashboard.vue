<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link } from '@inertiajs/vue3'
import { ref } from 'vue'
import { 
    DocumentDuplicateIcon, 
    ClockIcon, 
    CheckBadgeIcon,
    ChartPieIcon,
    ArrowRightIcon,
    DocumentTextIcon,
    PlusCircleIcon,
    BuildingLibraryIcon,
    UsersIcon,
    BookmarkSquareIcon,
    QrCodeIcon,
    SparklesIcon,
    ScaleIcon,
    FolderOpenIcon
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
    recent_acts: Array,
})

const activeTab = ref('certificates')

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
        naissance: 'Naissance',
        mariage: 'Mariage',
        deces: 'Décès',
    }
    return dictionary[type] || type
}

const typeColors = [
    'bg-[#1E690F]', 
    'bg-[#3D9426]', 
    'bg-[#F0C31E]', 
    'bg-[#D9A100]', 
    'bg-[#185709]', 
]

const barData = {
  labels: ['Naissances', 'Mariages', 'Décès'],
  datasets: [
    {
      label: 'Actes Enregistrés',
      backgroundColor: ['#1E690F', '#0284C7', '#D97706'],
      borderRadius: 10,
      data: [props.stats.births_count, props.stats.marriages_count, props.stats.deaths_count]
    }
  ]
}

const barOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: { display: false },
    tooltip: {
      backgroundColor: '#0F172A',
      titleFont: { family: 'Outfit, sans-serif', size: 12, weight: 'bold' },
      bodyFont: { family: 'Outfit, sans-serif', size: 12 },
      padding: 12,
      cornerRadius: 12
    }
  },
  scales: {
    y: {
      beginAtZero: true,
      grid: { color: '#F1F5F9' },
      ticks: {
        font: { family: 'Outfit, sans-serif', weight: '600', size: 11 },
        color: '#64748B'
      }
    },
    x: {
      grid: { display: false },
      ticks: {
        font: { family: 'Outfit, sans-serif', weight: '700', size: 12 },
        color: '#334155'
      }
    }
  }
}
</script>

<template>
    <Head title="Tableau de Bord — Mairie de Enampore" />

    <AuthenticatedLayout>
        <template #header>
            <div class="hidden"></div>
        </template>

        <div class="min-h-screen bg-slate-50/70 pb-16">
            <!-- Hero Banner Premium Executive -->
            <div class="relative bg-slate-900 pt-8 pb-20 overflow-hidden border-b border-slate-800" style="background: linear-gradient(135deg, #061A02 0%, #16530B 50%, #0A2903 100%);">
                <div class="absolute inset-0 opacity-15" style="background-image: radial-gradient(circle at 2px 2px, white 1px, transparent 0); background-size: 28px 28px;"></div>
                <div class="absolute top-0 right-1/4 w-96 h-96 rounded-full bg-emerald-500/10 blur-3xl pointer-events-none"></div>
                <div class="absolute bottom-0 left-10 w-80 h-80 rounded-full bg-amber-400/15 blur-3xl pointer-events-none"></div>

                <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                        <div>
                            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-white/10 border border-white/15 backdrop-blur-md mb-4">
                                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                                <span class="text-[11px] font-black text-white uppercase tracking-widest">Console d'État Civil &bull; Commune d'Enampore</span>
                            </div>
                            <h1 class="text-3xl sm:text-4xl lg:text-5xl font-black text-white tracking-tight leading-tight">
                                Bonjour, {{ $page.props.auth.user.name.split(' ')[0] }} <span class="inline-block animate-bounce text-amber-400">👋</span>
                            </h1>
                            <p class="text-emerald-100/80 font-medium mt-2 max-w-2xl text-sm sm:text-base leading-relaxed">
                                Synthèse de la plateforme : {{ stats.total_acts || 0 }} actes enregistrés, {{ stats.registries_open || 0 }} registres actifs et {{ stats.pending || 0 }} demande(s) en attente.
                            </p>
                        </div>
                        
                        <div class="flex flex-wrap items-center gap-3">
                            <Link 
                                :href="route('acts.naissance.index')"
                                class="inline-flex items-center justify-center gap-2 px-5 py-3.5 bg-white/10 hover:bg-white/20 text-white text-xs sm:text-sm font-black rounded-2xl backdrop-blur-md border border-white/20 transition-all duration-300 shadow-sm"
                            >
                                <FolderOpenIcon class="h-4 w-4 text-emerald-300" />
                                <span>Actes d'État Civil</span>
                            </Link>
                            <Link 
                                :href="route('certificates.verify')"
                                class="inline-flex items-center justify-center gap-2 px-5 py-3.5 bg-white/10 hover:bg-white/20 text-white text-xs sm:text-sm font-black rounded-2xl backdrop-blur-md border border-white/20 transition-all duration-300 shadow-sm"
                            >
                                <QrCodeIcon class="h-4 w-4 text-amber-300" />
                                <span>Vérification QR</span>
                            </Link>
                            <Link 
                                v-if="$page.props.auth.user.permissions?.includes('create-drafts')"
                                :href="route('civil-certificates.create')"
                                class="inline-flex items-center justify-center gap-2 px-6 py-3.5 text-[#0A2903] text-xs sm:text-sm font-black rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 hover:scale-105 active:scale-95"
                                style="background: linear-gradient(135deg, #F0C31E 0%, #FFD700 100%);"
                            >
                                <PlusCircleIcon class="h-5 w-5" />
                                <span>Nouvel Acte</span>
                            </Link>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contenu Principal -->
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-10 relative z-10">
                
                <!-- Bento Box : Les Métriques Clés Globale de la Plateforme -->
                <div class="grid grid-cols-1 md:grid-cols-12 gap-6 mb-8">
                    
                    <!-- Grand Bloc : Actions Requises Urgent -->
                    <div class="md:col-span-4 bg-white rounded-3xl p-6 sm:p-7 shadow-sm border border-slate-200/80 hover:shadow-md transition-all duration-300 flex flex-col justify-between relative overflow-hidden group">
                        <div class="absolute -right-8 -top-8 w-32 h-32 bg-amber-500/10 rounded-full blur-2xl group-hover:bg-amber-500/20 transition-colors pointer-events-none"></div>
                        
                        <div>
                            <div class="flex items-center justify-between mb-4">
                                <div class="h-12 w-12 rounded-2xl bg-amber-50 border border-amber-100 flex items-center justify-center shadow-xs">
                                    <ClockIcon class="h-6 w-6 text-amber-600" />
                                </div>
                                <span class="px-2.5 py-1 rounded-full bg-amber-50 text-amber-700 border border-amber-200/60 text-[10px] font-black uppercase tracking-wider">
                                    Priorité Haute
                                </span>
                            </div>
                            <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-1">Actions Requises</h3>
                            <div class="flex items-baseline gap-3">
                                <span class="text-5xl font-black text-slate-900 tracking-tight">{{ stats.pending }}</span>
                                <span class="text-xs font-bold text-amber-600 bg-amber-50 px-2.5 py-1 rounded-lg border border-amber-100">
                                    demande(s) en attente
                                </span>
                            </div>
                        </div>

                        <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between">
                            <p class="text-xs text-slate-500 font-medium">Validations administratives requises.</p>
                            <Link :href="route('civil-certificates.index', { status: 'pending' })" class="text-xs font-black text-amber-600 hover:text-amber-700 flex items-center gap-1">
                                Traiter <ArrowRightIcon class="h-3.5 w-3.5" />
                            </Link>
                        </div>
                    </div>

                    <!-- Cartes KPIs 2x2 des Registres & Volumes -->
                    <div class="md:col-span-8 grid grid-cols-1 sm:grid-cols-2 gap-5">
                        
                        <!-- Total Actes d'État Civil -->
                        <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-200/80 hover:border-emerald-500/40 transition-all duration-300 flex flex-col justify-between group">
                            <div class="flex justify-between items-center mb-3">
                                <div class="h-11 w-11 rounded-2xl bg-emerald-50 text-emerald-800 flex items-center justify-center border border-emerald-100 group-hover:bg-emerald-800 group-hover:text-white transition-colors duration-300">
                                    <FolderOpenIcon class="h-5 w-5" />
                                </div>
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Base Registres</span>
                            </div>
                            <div>
                                <h3 class="text-3xl font-black text-slate-900 tracking-tight">{{ stats.total_acts || 0 }}</h3>
                                <p class="text-xs font-semibold text-slate-500 mt-0.5">Actes d'état civil enregistrés au total</p>
                            </div>
                        </div>

                        <!-- Jugements d'Autorisation -->
                        <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-200/80 hover:border-emerald-500/40 transition-all duration-300 flex flex-col justify-between group">
                            <div class="flex justify-between items-center mb-3">
                                <div class="h-11 w-11 rounded-2xl bg-purple-50 text-purple-700 flex items-center justify-center border border-purple-100 group-hover:bg-purple-700 group-hover:text-white transition-colors duration-300">
                                    <ScaleIcon class="h-5 w-5" />
                                </div>
                                <span class="text-[10px] font-bold text-purple-700 bg-purple-50 px-2 py-0.5 rounded-md border border-purple-100 uppercase tracking-wider">Judiciaire</span>
                            </div>
                            <div>
                                <h3 class="text-3xl font-black text-slate-900 tracking-tight">{{ stats.judgments_count || 0 }}</h3>
                                <p class="text-xs font-semibold text-slate-500 mt-0.5">Actes issus de Jugement d'Autorisation</p>
                            </div>
                        </div>
                        
                        <!-- Naissances Enregistrées -->
                        <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-200/80 hover:border-emerald-500/40 transition-all duration-300 flex flex-col justify-between group">
                            <div class="flex justify-between items-center mb-3">
                                <div class="h-11 w-11 rounded-2xl bg-emerald-50 text-emerald-800 flex items-center justify-center border border-emerald-100">
                                    <DocumentTextIcon class="h-5 w-5" />
                                </div>
                                <span class="text-[10px] font-black text-emerald-700 uppercase tracking-widest">Naissances</span>
                            </div>
                            <div>
                                <h3 class="text-3xl font-black text-emerald-800 tracking-tight">{{ stats.births_count }}</h3>
                                <p class="text-xs font-semibold text-slate-500 mt-0.5">Actes de naissance enregistrés</p>
                            </div>
                        </div>

                        <!-- Mariages & Décès -->
                        <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-200/80 hover:border-emerald-500/40 transition-all duration-300 flex flex-col justify-between group">
                            <div class="flex justify-between items-center mb-3">
                                <div class="h-11 w-11 rounded-2xl bg-sky-50 text-sky-700 flex items-center justify-center border border-sky-100">
                                    <DocumentTextIcon class="h-5 w-5" />
                                </div>
                                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Mariages & Décès</span>
                            </div>
                            <div class="grid grid-cols-2 gap-2 pt-1">
                                <div class="bg-sky-50/70 p-2.5 rounded-2xl border border-sky-100/70">
                                    <span class="text-[10px] font-black text-sky-600 uppercase">Mariages</span>
                                    <p class="text-xl font-black text-sky-900">{{ stats.marriages_count }}</p>
                                </div>
                                <div class="bg-amber-50/70 p-2.5 rounded-2xl border border-amber-100/70">
                                    <span class="text-[10px] font-black text-amber-600 uppercase">Décès</span>
                                    <p class="text-xl font-black text-amber-900">{{ stats.deaths_count }}</p>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Section Indicateurs d'Administration & Registres -->
                <div class="mb-8">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h2 class="text-sm font-black text-slate-800 uppercase tracking-widest">Indicateurs de Performance et Administration</h2>
                            <p class="text-xs text-slate-500 font-medium">État des registres physiques, centres d'état civil et statuts des actes</p>
                        </div>
                        <span class="px-3 py-1 bg-white rounded-full text-xs font-bold text-slate-600 border border-slate-200 shadow-xs">
                            Centre Principal d'Enampore
                        </span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                        
                        <!-- Registres Ouverts -->
                        <div class="bg-white p-5 rounded-3xl shadow-sm border border-slate-200/80 hover:shadow-md transition-all">
                            <div class="flex items-center justify-between mb-3">
                                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Registres Ouverts</span>
                                <div class="h-8 w-8 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-700">
                                    <BookmarkSquareIcon class="h-4 w-4" />
                                </div>
                            </div>
                            <h4 class="text-3xl font-black text-slate-900 tracking-tight">{{ stats.registries_open }}</h4>
                            <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px]">
                                <span class="font-bold text-emerald-700 flex items-center gap-1">
                                    <CheckBadgeIcon class="h-3.5 w-3.5" /> {{ stats.acts_signe }} actes officiellement signés
                                </span>
                            </div>
                        </div>

                        <!-- Registres Clos -->
                        <div class="bg-white p-5 rounded-3xl shadow-sm border border-slate-200/80 hover:shadow-md transition-all">
                            <div class="flex items-center justify-between mb-3">
                                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Registres Clos</span>
                                <div class="h-8 w-8 rounded-xl bg-slate-100 flex items-center justify-center text-slate-600">
                                    <BookmarkSquareIcon class="h-4 w-4" />
                                </div>
                            </div>
                            <h4 class="text-3xl font-black text-slate-700 tracking-tight">{{ stats.registries_closed }}</h4>
                            <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px]">
                                <span class="font-bold text-amber-600 flex items-center gap-1">
                                    <ClockIcon class="h-3.5 w-3.5" /> {{ stats.acts_a_corriger }} à corriger
                                </span>
                            </div>
                        </div>

                        <!-- Centres Actifs -->
                        <div class="bg-white p-5 rounded-3xl shadow-sm border border-slate-200/80 hover:shadow-md transition-all">
                            <div class="flex items-center justify-between mb-3">
                                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Centres d'État Civil</span>
                                <div class="h-8 w-8 rounded-xl bg-indigo-50 flex items-center justify-center text-indigo-600">
                                    <BuildingLibraryIcon class="h-4 w-4" />
                                </div>
                            </div>
                            <h4 class="text-3xl font-black text-slate-900 tracking-tight">{{ stats.centers_count }}</h4>
                            <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px]">
                                <span class="font-bold text-indigo-600 flex items-center gap-1">
                                    <DocumentDuplicateIcon class="h-3.5 w-3.5" /> {{ stats.acts_valide }} validés hiérarchie
                                </span>
                            </div>
                        </div>

                        <!-- Utilisateurs System -->
                        <div class="bg-white p-5 rounded-3xl shadow-sm border border-slate-200/80 hover:shadow-md transition-all">
                            <div class="flex items-center justify-between mb-3">
                                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Utilisateurs</span>
                                <div class="h-8 w-8 rounded-xl bg-purple-50 flex items-center justify-center text-purple-600">
                                    <UsersIcon class="h-4 w-4" />
                                </div>
                            </div>
                            <h4 class="text-3xl font-black text-slate-900 tracking-tight">{{ stats.users_count }}</h4>
                            <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px]">
                                <span class="font-bold text-slate-500 flex items-center gap-1">
                                    <DocumentTextIcon class="h-3.5 w-3.5 text-slate-400" /> {{ stats.acts_draft }} brouillons d'actes
                                </span>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Section Graphiques & Activités Récentes -->
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                    
                    <!-- Colonne de Gauche : Graphiques & Répartition -->
                    <div class="lg:col-span-7 space-y-8">
                        
                        <!-- Graphique en Barres -->
                        <div class="bg-white rounded-3xl p-6 sm:p-7 shadow-sm border border-slate-200/80">
                            <div class="flex items-center justify-between mb-6">
                                <div>
                                    <h3 class="text-base font-black text-slate-900 tracking-tight">Volume Général des Actes</h3>
                                    <p class="text-xs text-slate-500 font-medium mt-0.5">Répartition par type d'actes civils enregistrés</p>
                                </div>
                                <div class="h-10 w-10 bg-slate-50 rounded-2xl flex items-center justify-center border border-slate-100">
                                    <ChartPieIcon class="h-5 w-5 text-slate-500" />
                                </div>
                            </div>
                            <div class="h-64">
                                <Bar :data="barData" :options="barOptions" />
                            </div>
                        </div>

                        <!-- Répartition des Types de Certificats -->
                        <div class="bg-white rounded-3xl p-6 sm:p-7 shadow-sm border border-slate-200/80">
                            <h3 class="text-sm font-black text-slate-900 tracking-tight flex items-center gap-2 mb-6">
                                <ChartPieIcon class="h-4 w-4 text-emerald-700" />
                                Demandes de Certificats Civils par Type
                            </h3>

                            <div class="space-y-4">
                                <div 
                                    v-for="(count, type, index) in stats.by_type" 
                                    :key="type" 
                                    class="space-y-1.5"
                                >
                                    <div class="flex justify-between items-center text-xs font-bold">
                                        <span class="text-slate-600 uppercase tracking-wider text-[10px]">{{ formatType(type) }}</span>
                                        <span class="text-slate-900 font-black">{{ count }}</span>
                                    </div>
                                    <div class="h-2 w-full bg-slate-100 rounded-full overflow-hidden">
                                        <div 
                                            class="h-full rounded-full transition-all duration-1000"
                                            :class="typeColors[index % typeColors.length]"
                                            :style="{ width: (stats.total > 0 ? (count / stats.total * 100) : 0) + '%' }"
                                        ></div>
                                    </div>
                                </div>
                                
                                <div v-if="!stats.by_type || Object.keys(stats.by_type).length === 0" class="text-center py-6">
                                    <p class="text-xs text-slate-400 font-medium">Pas de demandes de certificats saisies.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Colonne de Droite : Feed d'Activité Récente avec Onglets -->
                    <div class="lg:col-span-5">
                        <div class="bg-white rounded-3xl shadow-sm border border-slate-200/80 flex flex-col h-full overflow-hidden">
                            
                            <!-- Header avec Swapper d'Onglets -->
                            <div class="p-6 sm:p-7 border-b border-slate-100 bg-slate-50/50">
                                <div class="flex items-center justify-between mb-3">
                                    <h3 class="text-base font-black text-slate-900 tracking-tight">Activité Récente</h3>
                                    <span class="text-[10px] font-black text-emerald-800 uppercase tracking-widest px-2.5 py-1 bg-emerald-50 rounded-lg border border-emerald-100">
                                        En direct
                                    </span>
                                </div>
                                
                                <div class="flex p-1 bg-slate-200/60 rounded-xl">
                                    <button 
                                        @click="activeTab = 'certificates'"
                                        class="flex-1 py-1.5 text-[11px] font-black rounded-lg transition-all"
                                        :class="activeTab === 'certificates' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-600 hover:text-slate-900'"
                                    >
                                        Demandes Certificats ({{ recent ? recent.length : 0 }})
                                    </button>
                                    <button 
                                        @click="activeTab = 'acts'"
                                        class="flex-1 py-1.5 text-[11px] font-black rounded-lg transition-all"
                                        :class="activeTab === 'acts' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-600 hover:text-slate-900'"
                                    >
                                        Actes Enregistrés ({{ recent_acts ? recent_acts.length : 0 }})
                                    </button>
                                </div>
                            </div>
                            
                            <div class="flex-1 p-3">
                                <!-- Onglet 1 : Demandes de Certificats -->
                                <div v-if="activeTab === 'certificates'">
                                    <div v-if="recent && recent.length > 0" class="divide-y divide-slate-100">
                                        <Link 
                                            v-for="cert in recent" 
                                            :key="cert.id"
                                            :href="route('civil-certificates.show', cert.id)"
                                            class="flex items-center gap-4 p-3.5 rounded-2xl hover:bg-slate-50 transition-all duration-200 group"
                                        >
                                            <div class="h-11 w-11 rounded-2xl flex items-center justify-center flex-shrink-0 transition-colors duration-300 shadow-xs"
                                                :class="cert.status === 'validated' ? 'bg-emerald-50 text-emerald-700 group-hover:bg-emerald-700 group-hover:text-white' : 'bg-amber-50 text-amber-600 group-hover:bg-amber-500 group-hover:text-white'"
                                            >
                                                <DocumentTextIcon class="h-5 w-5" />
                                            </div>
                                            
                                            <div class="flex-1 min-w-0">
                                                <p class="text-xs sm:text-sm font-black text-slate-900 truncate group-hover:text-emerald-800 transition-colors">
                                                    {{ cert.applicant_first_name }} {{ cert.applicant_last_name }}
                                                </p>
                                                <div class="flex items-center gap-2 mt-0.5">
                                                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ formatType(cert.type) }}</span>
                                                </div>
                                            </div>
                                            
                                            <div class="flex flex-col items-end gap-1">
                                                <span 
                                                    class="px-2.5 py-0.5 rounded-full text-[9px] font-black uppercase tracking-widest border"
                                                    :class="cert.status === 'validated' ? 'bg-emerald-50 text-emerald-700 border-emerald-200/60' : 'bg-amber-50 text-amber-700 border-amber-200/60'"
                                                >
                                                    {{ cert.status === 'pending' ? 'Attente' : 'Validé' }}
                                                </span>
                                                <span class="text-[10px] font-mono font-semibold text-slate-400 group-hover:text-slate-700 transition-colors">{{ cert.reference_number }}</span>
                                            </div>
                                        </Link>
                                    </div>
                                    <div v-else class="p-12 text-center">
                                        <div class="h-14 w-14 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-3 text-slate-300">
                                            <DocumentTextIcon class="h-7 w-7" />
                                        </div>
                                        <p class="text-xs text-slate-500 font-medium">Aucune demande récente.</p>
                                    </div>
                                </div>

                                <!-- Onglet 2 : Actes d'État Civil -->
                                <div v-else>
                                    <div v-if="recent_acts && recent_acts.length > 0" class="divide-y divide-slate-100">
                                        <Link 
                                            v-for="act in recent_acts" 
                                            :key="act.id"
                                            :href="route('acts.naissance.show', act.id)"
                                            class="flex items-center gap-4 p-3.5 rounded-2xl hover:bg-slate-50 transition-all duration-200 group"
                                        >
                                            <div class="h-11 w-11 rounded-2xl bg-emerald-50 text-emerald-800 flex items-center justify-center flex-shrink-0 group-hover:bg-emerald-800 group-hover:text-white transition-colors">
                                                <FolderOpenIcon class="h-5 w-5" />
                                            </div>
                                            
                                            <div class="flex-1 min-w-0">
                                                <p class="text-xs sm:text-sm font-black text-slate-900 truncate group-hover:text-emerald-800 transition-colors">
                                                    {{ act.name }}
                                                </p>
                                                <div class="flex items-center gap-2 mt-0.5">
                                                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ formatType(act.type) }}</span>
                                                </div>
                                            </div>
                                            
                                            <div class="flex flex-col items-end gap-1">
                                                <span class="px-2 py-0.5 rounded-full text-[9px] font-black bg-emerald-50 text-emerald-700 border border-emerald-200/60 uppercase tracking-widest">
                                                    Inscrit
                                                </span>
                                                <span class="text-[10px] font-mono font-semibold text-slate-400">{{ act.ref }}</span>
                                            </div>
                                        </Link>
                                    </div>
                                    <div v-else class="p-12 text-center">
                                        <div class="h-14 w-14 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-3 text-slate-300">
                                            <FolderOpenIcon class="h-7 w-7" />
                                        </div>
                                        <p class="text-xs text-slate-500 font-medium">Aucun acte récent.</p>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
