<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { 
    UserPlusIcon, 
    PencilSquareIcon, 
    TrashIcon,
    UserCircleIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    users: Object,
    create_url: String,
});

const deleteUser = (deleteUrl) => {
    if (confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ? Cette action est irréversible.')) {
        router.delete(deleteUrl);
    }
};

const getRoleColor = (role) => {
    switch (role?.toLowerCase()) {
        case 'administrateur': return 'bg-red-100 text-red-700';
        case 'officier': return 'bg-blue-100 text-blue-700';
        case 'responsable': return 'bg-purple-100 text-purple-700';
        case 'agent': return 'bg-green-100 text-green-700';
        default: return 'bg-gray-100 text-gray-700';
    }
};
</script>

<template>
    <Head title="Gestion des Utilisateurs" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-gray-900 rounded-lg shadow-lg">
                        <UserCircleIcon class="h-6 w-6 text-white" />
                    </div>
                    <div>
                        <h2 class="font-black text-2xl text-gray-900 tracking-tight">Utilisateurs</h2>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">
                            Contrôle des accès et rôles SIG-EC
                        </p>
                    </div>
                </div>

                <Link
                    :href="create_url"
                    class="inline-flex items-center justify-center px-6 py-3 bg-blue-600 border border-transparent rounded-xl font-black text-xs text-white uppercase tracking-widest hover:bg-blue-700 shadow-xl shadow-blue-100 transition-all active:scale-95"
                >
                    <UserPlusIcon class="w-4 h-4 mr-2 stroke-[3]" />
                    Nouvel Utilisateur
                </Link>
            </div>
        </template>

        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden mt-6">
            <table class="min-w-full divide-y divide-gray-100">
                <thead class="bg-gray-50/50">
                    <tr>
                        <th class="px-8 py-5 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Utilisateur</th>
                        <th class="px-8 py-5 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Rôle</th>
                        <th class="px-8 py-5 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Date de création</th>
                        <th class="px-8 py-5 text-right text-[10px] font-black text-gray-400 uppercase tracking-widest">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    <tr v-for="user in users.data" :key="user.id" class="group hover:bg-gray-50/50 transition-colors">
                        <td class="px-8 py-5">
                            <div class="flex items-center gap-3">
                                <div class="h-10 w-10 rounded-full bg-blue-50 flex items-center justify-center text-blue-600 font-black text-sm">
                                    {{ user.name.substring(0, 2).toUpperCase() }}
                                </div>
                                <div>
                                    <div class="text-sm font-black text-gray-900">{{ user.name }}</div>
                                    <div class="text-[10px] font-bold text-gray-400">{{ user.email }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-8 py-5">
                            <span v-for="role in user.roles" :key="role.id" 
                                  class="px-3 py-1 inline-flex text-[9px] font-black uppercase rounded-full mr-1"
                                  :class="getRoleColor(role.name)">
                                {{ role.name }}
                            </span>
                        </td>
                        <td class="px-8 py-5 text-xs text-gray-500 font-bold">
                            {{ new Date(user.created_at).toLocaleDateString('fr-FR') }}
                        </td>
                        <td class="px-8 py-5 text-right space-x-2">
                            <!-- Use server-side pre-computed URLs to avoid Ziggy stale manifest -->
                            <Link :href="user.edit_url" class="inline-flex p-2 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-all">
                                <PencilSquareIcon class="h-5 w-5" />
                            </Link>
                            <button @click="deleteUser(user.delete_url)" class="inline-flex p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-all">
                                <TrashIcon class="h-5 w-5" />
                            </button>
                        </td>
                    </tr>
                    <tr v-if="users.data.length === 0">
                        <td colspan="4" class="px-8 py-20 text-center">
                            <div class="flex flex-col items-center gap-3">
                                <div class="h-12 w-12 bg-gray-50 rounded-full flex items-center justify-center">
                                    <UserCircleIcon class="h-6 w-6 text-gray-300" />
                                </div>
                                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Aucun utilisateur enregistré</p>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AuthenticatedLayout>
</template>
