<script setup>
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { 
    UserPlusIcon, 
    PencilSquareIcon, 
    TrashIcon,
    UserCircleIcon,
    NoSymbolIcon,
    CheckCircleIcon
} from '@heroicons/vue/24/outline';

const currentUserId = usePage().props.auth.user.id;

const props = defineProps({
    users: Object,
    create_url: String,
});

// --- Delete ---
const showDeleteModal = ref(false);
const pendingDeleteUrl = ref('');

const deleteUser = (deleteUrl) => {
    pendingDeleteUrl.value = deleteUrl;
    showDeleteModal.value = true;
};

const executeDelete = () => {
    showDeleteModal.value = false;
    if (pendingDeleteUrl.value) {
        router.delete(pendingDeleteUrl.value, {
            onFinish: () => { pendingDeleteUrl.value = ''; }
        });
    }
};

const cancelDelete = () => {
    showDeleteModal.value = false;
    pendingDeleteUrl.value = '';
};

// --- Suspend / Reactivate ---
const showStatusModal = ref(false);
const pendingStatusUser = ref(null);

const toggleStatus = (user) => {
    pendingStatusUser.value = user;
    showStatusModal.value = true;
};

const confirmToggleStatus = () => {
    showStatusModal.value = false;
    if (pendingStatusUser.value) {
        router.post(pendingStatusUser.value.toggle_status_url, {}, {
            onFinish: () => { pendingStatusUser.value = null; }
        });
    }
};

const cancelToggleStatus = () => {
    showStatusModal.value = false;
    pendingStatusUser.value = null;
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
                        <th class="px-8 py-5 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Statut</th>
                        <th class="px-8 py-5 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Date de création</th>
                        <th class="px-8 py-5 text-right text-[10px] font-black text-gray-400 uppercase tracking-widest">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    <tr v-for="user in users.data" :key="user.id" class="group hover:bg-gray-50/50 transition-colors" :class="{ 'opacity-60': !user.is_active }">
                        <td class="px-8 py-5">
                            <div class="flex items-center gap-3">
                                <img v-if="user.profile_photo_url" :src="user.profile_photo_url" alt="" class="h-10 w-10 rounded-full object-cover shadow-sm border border-gray-100" />
                                <div v-else class="h-10 w-10 rounded-full bg-blue-50 flex items-center justify-center text-blue-600 font-black text-sm border border-blue-100">
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
                        <td class="px-8 py-5">
                            <span v-if="user.is_active" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[9px] font-black uppercase bg-green-100 text-green-700">
                                <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>
                                Actif
                            </span>
                            <span v-else class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[9px] font-black uppercase bg-red-100 text-red-700">
                                <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                                Suspendu
                            </span>
                        </td>
                        <td class="px-8 py-5 text-xs text-gray-500 font-bold">
                            {{ new Date(user.created_at).toLocaleDateString('fr-FR') }}
                        </td>
                        <td class="px-8 py-5 text-right space-x-2">
                            <Link :href="user.edit_url" class="inline-flex p-2 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-all" title="Modifier">
                                <PencilSquareIcon class="h-5 w-5" />
                            </Link>
                            <!-- Suspend / Reactivate (hidden for current user) -->
                            <button
                                v-if="user.id !== currentUserId"
                                @click="toggleStatus(user)"
                                class="inline-flex p-2 rounded-lg transition-all"
                                :class="user.is_active
                                    ? 'text-gray-400 hover:text-orange-600 hover:bg-orange-50'
                                    : 'text-gray-400 hover:text-green-600 hover:bg-green-50'"
                                :title="user.is_active ? 'Suspendre le compte' : 'Réactiver le compte'"
                            >
                                <NoSymbolIcon v-if="user.is_active" class="h-5 w-5" />
                                <CheckCircleIcon v-else class="h-5 w-5" />
                            </button>
                            <button @click="deleteUser(user.delete_url)" class="inline-flex p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-all" title="Supprimer">
                                <TrashIcon class="h-5 w-5" />
                            </button>
                        </td>
                    </tr>
                    <tr v-if="users.data.length === 0">
                        <td colspan="5" class="px-8 py-20 text-center">
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

        <!-- Custom Delete Modal -->
        <Teleport to="body">
          <Transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
          >
            <div v-if="showDeleteModal" class="fixed inset-0 z-50 overflow-y-auto bg-gray-900/60 backdrop-blur-sm flex items-center justify-center p-4">
              <div class="bg-white rounded-3xl overflow-hidden shadow-2xl transform transition-all max-w-lg w-full border border-gray-100">
                <div class="p-8 flex items-start gap-6">
                  <div class="flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-2xl bg-red-50 text-red-600">
                    <TrashIcon class="h-6 w-6" />
                  </div>
                  <div class="flex-1 min-w-0">
                    <h3 class="text-lg font-black text-gray-900 leading-tight">Suppression de l'utilisateur</h3>
                    <p class="mt-2 text-sm text-gray-500 font-medium">
                      Êtes-vous sûr de vouloir supprimer cet utilisateur ? Cette action est irréversible et révoquera immédiatement tous ses accès à la plateforme.
                    </p>
                    <div class="mt-8 flex justify-end gap-3">
                      <button type="button" @click="cancelDelete" class="px-6 py-3 bg-white border border-gray-200 rounded-xl font-black text-[10px] text-gray-500 uppercase tracking-widest hover:bg-gray-50 transition-all active:scale-95">
                        Annuler
                      </button>
                      <button type="button" @click="executeDelete" class="px-6 py-3 bg-red-600 hover:bg-red-700 text-white rounded-xl font-black text-[10px] uppercase tracking-widest shadow-xl shadow-red-100 transition-all active:scale-95">
                        Supprimer
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </Transition>
        </Teleport>

        <!-- Suspend / Reactivate Modal -->
        <Teleport to="body">
          <Transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
          >
            <div v-if="showStatusModal && pendingStatusUser" class="fixed inset-0 z-50 overflow-y-auto bg-gray-900/60 backdrop-blur-sm flex items-center justify-center p-4">
              <div class="bg-white rounded-3xl overflow-hidden shadow-2xl transform transition-all max-w-lg w-full border border-gray-100">
                <div class="p-8 flex items-start gap-6">
                  <div
                    class="flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-2xl"
                    :class="pendingStatusUser.is_active ? 'bg-orange-50 text-orange-600' : 'bg-green-50 text-green-600'"
                  >
                    <NoSymbolIcon v-if="pendingStatusUser.is_active" class="h-6 w-6" />
                    <CheckCircleIcon v-else class="h-6 w-6" />
                  </div>
                  <div class="flex-1 min-w-0">
                    <h3 class="text-lg font-black text-gray-900 leading-tight">
                      {{ pendingStatusUser.is_active ? 'Suspendre le compte' : 'Réactiver le compte' }}
                    </h3>
                    <p class="mt-2 text-sm text-gray-500 font-medium">
                      <template v-if="pendingStatusUser.is_active">
                        Voulez-vous suspendre le compte de <strong>{{ pendingStatusUser.name }}</strong> ? 
                        L'utilisateur ne pourra plus se connecter à la plateforme tant que son compte est suspendu.
                      </template>
                      <template v-else>
                        Voulez-vous réactiver le compte de <strong>{{ pendingStatusUser.name }}</strong> ? 
                        L'utilisateur pourra à nouveau se connecter à la plateforme.
                      </template>
                    </p>
                    <div class="mt-8 flex justify-end gap-3">
                      <button type="button" @click="cancelToggleStatus" class="px-6 py-3 bg-white border border-gray-200 rounded-xl font-black text-[10px] text-gray-500 uppercase tracking-widest hover:bg-gray-50 transition-all active:scale-95">
                        Annuler
                      </button>
                      <button
                        type="button"
                        @click="confirmToggleStatus"
                        class="px-6 py-3 text-white rounded-xl font-black text-[10px] uppercase tracking-widest shadow-xl transition-all active:scale-95"
                        :class="pendingStatusUser.is_active
                            ? 'bg-orange-500 hover:bg-orange-600 shadow-orange-100'
                            : 'bg-green-600 hover:bg-green-700 shadow-green-100'"
                      >
                        {{ pendingStatusUser.is_active ? 'Suspendre' : 'Réactiver' }}
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </Transition>
        </Teleport>
    </AuthenticatedLayout>
</template>
