<script setup>
import { ref, onMounted, computed } from 'vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head } from '@inertiajs/vue3'
import DoughnutChart from '@/Components/Charts/DoughnutChart.vue'
import BarChart from '@/Components/Charts/BarChart.vue'
import api from '@/services/api'
import { 
    UsersIcon, 
    AcademicCapIcon, 
    ComputerDesktopIcon, 
    ScaleIcon,
    ExclamationCircleIcon,
    WrenchIcon,
    ArrowTrendingUpIcon,
    ArrowPathIcon,
    ChartBarIcon,
    BoltIcon,
    BriefcaseIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
    kpis: Object
})

const dashboardKpis = ref(props.kpis)
const isLoading = ref(false)

const fetchStats = async () => {
    isLoading.value = true
    try {
        const response = await api.get('/stats/director')
        dashboardKpis.value = {
            ...dashboardKpis.value,
            total_learners: response.data.total_trained,
            attendance_rate: response.data.attendance_rate,
            operational_hardware: response.data.operational_assets,
            gender_parity: {
                male: response.data.gender_distribution[0],
                female: response.data.gender_distribution[1],
                ratio: response.data.parity_rate
            },
            module_validation_rates: response.data.validation_by_module || {}
        }
    } catch (error) {
        console.error('Erreur API:', error)
    } finally {
        isLoading.value = false
    }
}

const moduleLabels = computed(() => {
    if (!dashboardKpis.value || !dashboardKpis.value.module_validation_rates) return []
    return Object.keys(dashboardKpis.value.module_validation_rates)
})

const moduleData = computed(() => {
    if (!dashboardKpis.value || !dashboardKpis.value.module_validation_rates) return []
    return Object.values(dashboardKpis.value.module_validation_rates)
})

onMounted(() => {
    // Animation entry logic could be added here if needed
})
</script>

<template>
    <Head title="Tableau de Bord Stratégique" />

    <AuthenticatedLayout>
        <div class="min-h-screen bg-[#f8fafc] pb-12">
            <!-- Hero Header with Glassmorphism -->
            <div class="relative overflow-hidden bg-white border-b border-gray-100 px-4 sm:px-6 lg:px-8 py-10 mb-8">
                <div class="absolute top-0 right-0 -mt-20 -mr-20 w-96 h-96 bg-emerald-50 rounded-full blur-3xl opacity-50"></div>
                <div class="absolute bottom-0 left-0 -mb-20 -ml-20 w-72 h-72 bg-blue-50 rounded-full blur-3xl opacity-30"></div>
                
                <div class="max-w-7xl mx-auto relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
                    <div>
                        <div class="flex items-center gap-2 mb-2">
                            <span class="px-3 py-1 bg-emerald-100 text-emerald-700 text-[10px] font-black uppercase tracking-widest rounded-full">Vision 360°</span>
                        </div>
                        <h1 class="text-4xl font-black text-gray-900 tracking-tight flex items-center gap-3">
                            Tableau de <span class="bg-clip-text text-transparent bg-gradient-to-r from-emerald-600 to-blue-600">Bord Stratégique</span>
                        </h1>
                        <p class="text-gray-500 mt-2 max-w-2xl font-medium">Pilotage intelligent de la performance académique et logistique du CRE Kolda.</p>
                    </div>
                    
                    <div class="flex items-center gap-4">
                        <button 
                            @click="fetchStats"
                            :disabled="isLoading"
                            class="group flex items-center gap-2 px-5 py-3 bg-white border border-gray-200 text-gray-700 rounded-2xl font-bold shadow-sm hover:shadow-md hover:border-emerald-200 transition-all active:scale-95 disabled:opacity-50"
                        >
                            <ArrowPathIcon class="h-5 w-5 text-emerald-500" :class="{ 'animate-spin': isLoading }" />
                            <span>Actualiser</span>
                        </button>
                        <button class="flex items-center gap-2 px-6 py-3 bg-gray-900 text-white rounded-2xl font-bold shadow-xl shadow-gray-200 hover:bg-black hover:-translate-y-0.5 transition-all active:scale-95">
                            <ArrowTrendingUpIcon class="h-5 w-5 text-emerald-400" />
                            <span>Rapport d'Activité</span>
                        </button>
                    </div>
                </div>
            </div>

            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8 animate-in fade-in duration-700">
                
                <!-- KPI Grid: Futuristic Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <!-- Total Formés -->
                    <div class="group relative bg-white p-6 rounded-[2rem] border border-gray-100 shadow-sm transition-all hover:shadow-xl hover:-translate-y-1">
                        <div class="absolute top-4 right-4 h-10 w-10 bg-blue-50 rounded-xl flex items-center justify-center text-blue-600 group-hover:bg-blue-600 group-hover:text-white transition-colors">
                            <UsersIcon class="h-6 w-6" />
                        </div>
                        <p class="text-xs font-black text-gray-400 uppercase tracking-widest mb-1">Total Apprenants</p>
                        <h3 class="text-3xl font-black text-gray-900 leading-none mb-4">{{ dashboardKpis.total_learners }}</h3>
                        <div class="flex items-center gap-2 text-xs font-bold text-emerald-600">
                            <span class="flex items-center justify-center h-5 w-5 bg-emerald-50 rounded-full">↑</span>
                            <span>+12% ce mois</span>
                        </div>
                    </div>

                    <!-- Assiduité -->
                    <div class="group relative bg-white p-6 rounded-[2rem] border border-gray-100 shadow-sm transition-all hover:shadow-xl hover:-translate-y-1">
                        <div class="absolute top-4 right-4 h-10 w-10 bg-emerald-50 rounded-xl flex items-center justify-center text-emerald-600 group-hover:bg-emerald-600 group-hover:text-white transition-colors">
                            <BoltIcon class="h-6 w-6" />
                        </div>
                        <p class="text-xs font-black text-gray-400 uppercase tracking-widest mb-1">Taux d'Assiduité</p>
                        <h3 class="text-3xl font-black text-gray-900 leading-none mb-4">{{ dashboardKpis.attendance_rate }}%</h3>
                        <div class="w-full bg-gray-100 h-1.5 rounded-full overflow-hidden">
                            <div class="bg-emerald-500 h-full rounded-full transition-all duration-1000" :style="{ width: dashboardKpis.attendance_rate + '%' }"></div>
                        </div>
                    </div>

                    <!-- Matériel -->
                    <div class="group relative bg-white p-6 rounded-[2rem] border border-gray-100 shadow-sm transition-all hover:shadow-xl hover:-translate-y-1">
                        <div class="absolute top-4 right-4 h-10 w-10 bg-indigo-50 rounded-xl flex items-center justify-center text-indigo-600 group-hover:bg-indigo-600 group-hover:text-white transition-colors">
                            <ComputerDesktopIcon class="h-6 w-6" />
                        </div>
                        <p class="text-xs font-black text-gray-400 uppercase tracking-widest mb-1">Parc Opérationnel</p>
                        <h3 class="text-3xl font-black text-gray-900 leading-none mb-4">{{ dashboardKpis.operational_hardware }}%</h3>
                        <p class="text-xs font-medium text-gray-500 italic">Maintenance préventive recommandée</p>
                    </div>

                    <!-- Parité -->
                    <div class="group relative bg-white p-6 rounded-[2rem] border border-gray-100 shadow-sm transition-all hover:shadow-xl hover:-translate-y-1">
                        <div class="absolute top-4 right-4 h-10 w-10 bg-pink-50 rounded-xl flex items-center justify-center text-pink-600 group-hover:bg-pink-600 group-hover:text-white transition-colors">
                            <ScaleIcon class="h-6 w-6" />
                        </div>
                        <p class="text-xs font-black text-gray-400 uppercase tracking-widest mb-1">Indice de Parité</p>
                        <h3 class="text-3xl font-black text-gray-900 leading-none mb-4">{{ dashboardKpis.gender_parity.ratio }}%</h3>
                        <div class="flex gap-1">
                            <div class="h-1 flex-1 bg-pink-400 rounded-full" :style="{ opacity: dashboardKpis.gender_parity.ratio/100 }"></div>
                            <div class="h-1 flex-1 bg-blue-400 rounded-full" :style="{ opacity: (100 - dashboardKpis.gender_parity.ratio)/100 }"></div>
                        </div>
                    </div>
                </div>

                <!-- Charts & Visualizations -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Gender Distribution Card -->
                    <div class="bg-white p-8 rounded-[2.5rem] border border-gray-100 shadow-sm flex flex-col items-center">
                        <div class="w-full flex items-center justify-between mb-8">
                            <h2 class="text-xl font-black text-gray-900 tracking-tight">Démographie</h2>
                            <div class="h-8 w-8 bg-pink-50 text-pink-500 rounded-lg flex items-center justify-center">
                                <UsersIcon class="h-4 w-4" />
                            </div>
                        </div>
                        <div class="w-full max-w-[240px] mb-6">
                            <DoughnutChart :male="dashboardKpis.gender_parity.male" :female="dashboardKpis.gender_parity.female" />
                        </div>
                        <div class="w-full mt-auto pt-6 border-t border-gray-50 flex items-center justify-around">
                            <div class="flex items-center gap-3">
                                <div class="w-2 h-2 rounded-full bg-pink-500"></div>
                                <p class="text-sm font-bold text-gray-900">Femmes: <span class="text-pink-600">{{ dashboardKpis.gender_parity.female }}</span> <span class="text-xs text-gray-400">({{ dashboardKpis.gender_parity.ratio }}%)</span></p>
                            </div>
                            <div class="h-4 w-px bg-gray-200"></div>
                            <div class="flex items-center gap-3">
                                <div class="w-2 h-2 rounded-full bg-blue-500"></div>
                                <p class="text-sm font-bold text-gray-900">Hommes: <span class="text-blue-600">{{ dashboardKpis.gender_parity.male }}</span> <span class="text-xs text-gray-400">({{ 100 - dashboardKpis.gender_parity.ratio }}%)</span></p>
                            </div>
                        </div>
                    </div>

                    <!-- Performance by Module Card -->
                    <div class="lg:col-span-2 bg-white p-8 rounded-[2.5rem] border border-gray-100 shadow-sm">
                        <div class="w-full flex items-center justify-between mb-8">
                            <h2 class="text-xl font-black text-gray-900 tracking-tight">Certifications par Module</h2>
                            <div class="h-8 w-8 bg-indigo-50 text-indigo-500 rounded-lg flex items-center justify-center">
                                <AcademicCapIcon class="h-4 w-4" />
                            </div>
                        </div>
                        <div class="h-[300px]">
                            <BarChart :labels="moduleLabels" :data="moduleData" />
                        </div>
                    </div>
                </div>

                <!-- Strategic Alerts & Operational Status -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Risk Alerts -->
                    <div class="glass-card bg-white/70 backdrop-blur-xl rounded-[2.5rem] border border-red-100 shadow-xl shadow-red-50/20 overflow-hidden">
                        <div class="p-8 border-b border-red-50 flex items-center justify-between bg-gradient-to-r from-red-50/50 to-transparent">
                            <div>
                                <h2 class="text-xl font-black text-red-900 tracking-tight">Alertes Décrochage</h2>
                                <p class="text-sm text-red-500 font-medium">Élèves avec ≥ 2 absences non justifiées</p>
                            </div>
                            <div class="h-12 w-12 bg-red-600 text-white rounded-2xl flex items-center justify-center shadow-lg shadow-red-200">
                                <ExclamationCircleIcon class="h-7 w-7" />
                            </div>
                        </div>
                        <div class="p-4">
                            <div v-if="dashboardKpis.alerts.learners_at_risk.length > 0" class="space-y-3">
                                <div v-for="risk in dashboardKpis.alerts.learners_at_risk" :key="risk.user_id" class="p-4 bg-white border border-red-50 rounded-2xl flex items-center justify-between hover:border-red-200 transition-colors">
                                    <div class="flex items-center gap-3">
                                        <div class="h-10 w-10 bg-red-50 rounded-full flex items-center justify-center text-red-600 font-black text-sm">
                                            {{ risk.user.name.charAt(0) }}
                                        </div>
                                        <div>
                                            <p class="font-bold text-gray-900">{{ risk.user.name }}</p>
                                            <p class="text-[10px] text-gray-400 uppercase tracking-widest font-black">{{ risk.user.email.split('@')[0] }}</p>
                                        </div>
                                    </div>
                                    <div class="flex flex-col items-end">
                                        <span class="text-red-600 font-black text-xl leading-none">{{ risk.total_absences }}</span>
                                        <span class="text-[8px] font-black text-red-400 uppercase tracking-widest">Absences</span>
                                    </div>
                                </div>
                            </div>
                            <div v-else class="py-12 flex flex-col items-center text-gray-400 gap-3">
                                <div class="h-16 w-16 bg-emerald-50 text-emerald-500 rounded-full flex items-center justify-center">
                                    <BoltIcon class="h-8 w-8" />
                                </div>
                                <p class="font-bold italic">Excellente assiduité globale</p>
                            </div>
                        </div>
                    </div>

                    <!-- Hardware Status -->
                    <div class="glass-card bg-white/70 backdrop-blur-xl rounded-[2.5rem] border border-orange-100 shadow-xl shadow-orange-50/20 overflow-hidden">
                        <div class="p-8 border-b border-orange-50 flex items-center justify-between bg-gradient-to-r from-orange-50/50 to-transparent">
                            <div>
                                <h2 class="text-xl font-black text-orange-900 tracking-tight">Maintenance Parc</h2>
                                <p class="text-sm text-orange-500 font-medium">Unités nécessitant une intervention</p>
                            </div>
                            <div class="h-12 w-12 bg-orange-500 text-white rounded-2xl flex items-center justify-center shadow-lg shadow-orange-200">
                                <WrenchIcon class="h-7 w-7" />
                            </div>
                        </div>
                        <div class="p-4">
                            <div v-if="dashboardKpis.alerts.broken_assets.length > 0" class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div v-for="asset in dashboardKpis.alerts.broken_assets" :key="asset.id" class="p-4 bg-white border border-orange-50 rounded-2xl hover:border-orange-200 transition-colors">
                                    <p class="font-bold text-gray-900 text-sm truncate">{{ asset.nom }}</p>
                                    <div class="flex items-center justify-between mt-2">
                                        <span class="text-[10px] font-mono text-gray-400">{{ asset.serie }}</span>
                                        <span class="px-2 py-0.5 bg-orange-100 text-orange-700 text-[8px] font-black uppercase rounded">Hors Service</span>
                                    </div>
                                </div>
                            </div>
                            <div v-else class="py-12 flex flex-col items-center text-gray-400 gap-3">
                                <div class="h-16 w-16 bg-emerald-50 text-emerald-500 rounded-full flex items-center justify-center">
                                    <ComputerDesktopIcon class="h-8 w-8" />
                                </div>
                                <p class="font-bold italic">Tout le parc est fonctionnel</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.glass-card {
    transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
}
.animate-in {
    animation-duration: 700ms;
}
</style>
