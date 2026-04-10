<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link } from '@inertiajs/vue3'
import { 
    BookOpenIcon, 
    AcademicCapIcon, 
    PlayCircleIcon,
    CheckCircleIcon,
    ClockIcon,
    ArrowRightIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
    modules: Array
})
</script>

<template>
    <Head title="Mes Cours" />

    <AuthenticatedLayout>
        <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
            <header class="mb-12">
                <h1 class="text-4xl font-extrabold text-gray-900 tracking-tight">Mes Parcours de Formation</h1>
                <p class="mt-4 text-lg text-gray-500 max-w-2xl">
                    Accédez à vos modules, suivez votre progression et accédez aux ressources de chaque chapitre.
                </p>
            </header>

            <div v-if="modules && modules.length > 0" class="space-y-12">
                <div v-for="module in modules" :key="module.id" class="bg-white rounded-[3rem] border border-gray-100 shadow-sm overflow-hidden border-2 border-transparent hover:border-blue-500/10 transition-all duration-500 group">
                    <div class="p-8 md:p-12">
                        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-10">
                            <div>
                                <div class="inline-flex items-center gap-2 px-4 py-2 bg-blue-50 text-blue-700 rounded-full text-xs font-black uppercase tracking-widest mb-4">
                                    <AcademicCapIcon class="h-4 w-4" />
                                    {{ module.code_module }}
                                </div>
                                <h2 class="text-3xl font-black text-gray-900 tracking-tight group-hover:text-blue-600 transition-colors">
                                    {{ module.titre }}
                                </h2>
                            </div>
                            <div class="flex items-center gap-8">
                                <div class="text-center">
                                    <div class="text-2xl font-black text-gray-900">{{ module.chapters?.length || 0 }}</div>
                                    <div class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Chapitres</div>
                                </div>
                                <div class="h-10 w-px bg-gray-100"></div>
                                <div class="text-center">
                                    <div class="text-2xl font-black text-blue-600">{{ module.quota_heures }}h</div>
                                    <div class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Temps estimé</div>
                                </div>
                            </div>
                        </div>

                        <!-- Chapters List -->
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <Link 
                                v-for="chapter in module.chapters" 
                                :key="chapter.id"
                                :href="route('student.courses.show', [module.id, chapter.id])"
                                class="flex flex-col p-6 rounded-[2rem] bg-gray-50 hover:bg-gray-900 hover:text-white transition-all duration-300 group/item relative overflow-hidden"
                            >
                                <div class="relative z-10">
                                    <div class="flex items-center justify-between mb-4">
                                        <span class="text-[10px] font-black text-blue-600 group-hover/item:text-blue-400 uppercase tracking-widest">
                                            Chapitre {{ chapter.ordre }}
                                        </span>
                                        <PlayCircleIcon class="h-6 w-6 text-gray-300 group-hover/item:text-blue-400" />
                                    </div>
                                    <h3 class="text-lg font-bold leading-tight mb-4">{{ chapter.titre }}</h3>
                                    
                                    <div class="flex items-center justify-between mt-auto pt-4">
                                        <div class="flex items-center gap-2 text-xs font-medium text-gray-400">
                                            <ClockIcon class="h-4 w-4" />
                                            En cours
                                        </div>
                                        <ArrowRightIcon class="h-4 w-4 transform group-hover/item:translate-x-1 transition-transform" />
                                    </div>
                                </div>
                                
                                <!-- Background Decorative Element -->
                                <div class="absolute -right-4 -bottom-4 h-24 w-24 bg-blue-500/5 group-hover/item:bg-blue-500/10 rounded-full blur-2xl transition-all"></div>
                            </Link>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else class="text-center py-24 bg-white rounded-[3rem] border border-gray-100 border-dashed border-2">
                <div class="h-24 w-24 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-8">
                    <BookOpenIcon class="h-12 w-12 text-gray-200" />
                </div>
                <h3 class="text-2xl font-black text-gray-900 mb-2">Aucun cours disponible</h3>
                <p class="text-gray-500 font-medium">Aucun module actif n'est disponible pour le moment.</p>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
