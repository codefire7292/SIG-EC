<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head } from '@inertiajs/vue3'
import { 
    ChartBarIcon, 
    ArrowUpIcon, 
    ArrowDownIcon,
    CalendarDaysIcon,
    PresentationChartLineIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
    growth_data: Array,
    module_performance: Array,
    attendance_trends: Array
})
</script>

<template>
    <Head title="Statistiques & Rapports" />

    <AuthenticatedLayout>
        <div class="max-w-7xl mx-auto py-8 px-4">
            <header class="mb-12">
                <h1 class="text-4xl font-black text-gray-900 tracking-tight mb-2">Statistiques</h1>
                <p class="text-xl text-gray-500 font-medium tracking-tight text-balance max-w-2xl">Rapports détaillés sur la croissance, la performance des modules et l'engagement des apprenants.</p>
            </header>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-12">
                <!-- User Growth -->
                <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-gray-100">
                    <div class="flex items-center justify-between mb-8">
                        <h2 class="text-xl font-black text-gray-900 tracking-tight flex items-center gap-3">
                            <PresentationChartLineIcon class="h-6 w-6 text-indigo-600" />
                            Croissance Utilisateurs
                        </h2>
                        <span class="px-3 py-1 bg-green-50 text-green-600 rounded-lg text-xs font-black flex items-center gap-1">
                            <ArrowUpIcon class="h-3 w-3" /> 12%
                        </span>
                    </div>
                    <div class="h-64 flex items-end gap-3 px-4">
                        <div v-for="item in growth_data" :key="item.month" class="flex-1 flex flex-col items-center gap-4 group">
                            <div class="w-full bg-indigo-50 rounded-2xl relative overflow-hidden group-hover:bg-indigo-100 transition-colors" :style="{ height: (Math.max(item.count, 1) * 20) + 'px' }">
                                <div class="absolute inset-0 bg-indigo-600 translate-y-full group-hover:translate-y-0 transition-transform duration-500"></div>
                            </div>
                            <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">{{ item.month }}</span>
                        </div>
                    </div>
                </div>

                <!-- Module Performance -->
                <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-gray-100">
                    <h2 class="text-xl font-black text-gray-900 tracking-tight flex items-center gap-3 mb-8">
                        <ChartBarIcon class="h-6 w-6 text-orange-600" />
                        Certifications par Module
                    </h2>
                    <div class="space-y-6">
                        <div v-for="module in module_performance" :key="module.name" class="space-y-2">
                            <div class="flex justify-between text-sm">
                                <span class="font-bold text-gray-700">{{ module.name }}</span>
                                <span class="font-black text-indigo-600">{{ module.certificates }}</span>
                            </div>
                            <div class="h-2 bg-gray-50 rounded-full overflow-hidden">
                                <div class="h-full bg-indigo-600 rounded-full" :style="{ width: (module.certificates * 5) + '%' }"></div>
                            </div>
                        </div>
                        <div v-if="module_performance.length === 0" class="text-center py-12 text-gray-400 italic">
                            Aucune donnée de performance disponible.
                        </div>
                    </div>
                </div>
            </div>

            <!-- Attendance Trend -->
            <div class="bg-gray-900 p-10 rounded-[3rem] text-white shadow-2xl shadow-gray-200">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-12">
                    <div>
                        <h2 class="text-3xl font-black tracking-tight mb-2">Taux d'Émargement</h2>
                        <p class="text-gray-400 font-medium">Evolution hebdomadaire de la présence physique.</p>
                    </div>
                    <div class="flex items-center gap-4">
                         <div class="text-center">
                            <p class="text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1">Moyenne</p>
                            <p class="text-3xl font-black">84%</p>
                         </div>
                         <div class="h-10 w-px bg-gray-800"></div>
                         <div class="text-center">
                            <p class="text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1">Cible</p>
                            <p class="text-3xl font-black text-gray-500">90%</p>
                         </div>
                    </div>
                </div>
                
                <div class="grid grid-cols-4 gap-4 h-48 items-end">
                    <div v-for="trend in attendance_trends" :key="trend.week" class="relative group">
                        <div class="w-full bg-white/5 rounded-2xl group-hover:bg-white/10 transition-all cursor-pointer flex flex-col items-center justify-end p-4 gap-2" :style="{ height: trend.rate + '%' }">
                            <span class="text-xs font-black opacity-0 group-hover:opacity-100 transition-opacity">{{ trend.rate }}%</span>
                            <div class="w-2 h-2 rounded-full bg-indigo-500 shadow-[0_0_15px_rgba(99,102,241,0.5)]"></div>
                        </div>
                        <p class="text-center text-[10px] font-black text-gray-500 uppercase tracking-widest mt-4">{{ trend.week }}</p>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
