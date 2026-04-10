<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link } from '@inertiajs/vue3'
import { ChevronRightIcon, UserGroupIcon } from '@heroicons/vue/24/outline'

defineProps({
    groups: Array
})
</script>

<template>
    <Head title="Émargement - Sélection du groupe" />

    <AuthenticatedLayout>
        <div class="max-w-4xl mx-auto py-6 px-4">
            <h1 class="text-2xl font-bold text-gray-900 mb-6">Mes Groupes pour l'émargement</h1>

            <div v-if="groups.length === 0" class="bg-white rounded-xl p-8 text-center shadow-sm border border-gray-100">
                <UserGroupIcon class="h-12 w-12 text-gray-300 mx-auto mb-4" />
                <p class="text-gray-500">Aucun groupe ne vous est affecté pour le moment.</p>
            </div>

            <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <Link 
                    v-for="group in groups" 
                    :key="group.id"
                    :href="route('attendances.take', group.id)"
                    class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 hover:border-blue-300 transition-all group"
                >
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-lg font-bold text-gray-900 group-hover:text-blue-600 transition-colors">
                                {{ group.nom_groupe }}
                            </h2>
                            <p class="text-sm text-gray-500 mt-1">{{ group.module.nom_module }}</p>
                            <div class="mt-4 inline-flex items-center text-xs font-semibold px-2.5 py-0.5 rounded-full bg-blue-50 text-blue-700">
                                {{ group.annee_academique }}
                            </div>
                        </div>
                        <ChevronRightIcon class="h-6 w-6 text-gray-300 group-hover:text-blue-500 transition-colors" />
                    </div>
                </Link>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
