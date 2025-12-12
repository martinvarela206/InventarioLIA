@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 bg-white border-b border-gray-200">
            <h2 class="text-2xl font-bold mb-4 text-[#dba800] text-center">Gestión de Usuarios</h2>

            <div class="overflow-x-auto shadow-md rounded-lg">
                <table class="min-w-full leading-normal">
                    <thead>
                        <tr class="bg-amber-400 text-amber-900 uppercase text-sm leading-normal">
                            <th class="px-5 py-3 text-left font-semibold tracking-wider">
                                Nombre y Apellido
                            </th>
                            <th class="px-5 py-3 text-left font-semibold tracking-wider">
                                Roles
                            </th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-900 text-sm font-medium">
                        @foreach($users as $user)
                        <tr class="odd:bg-amber-50 even:bg-amber-100 border-b border-gray-300 hover:bg-amber-200 cursor-pointer transition-colors duration-200" onclick="window.location='{{ route('users.edit', $user) }}'">
                            <td class="px-5 py-5 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="ml-3">
                                        <p class="whitespace-no-wrap font-semibold text-amber-900">
                                            {{ $user->nombre }}
                                        </p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-5 whitespace-nowrap" onclick="event.stopPropagation()">
                                <div class="flex space-x-2">
                                    @foreach($roles as $role)
                                        @php
                                            $hasRole = $user->hasRole($role);
                                            $roleLabel = ucfirst(str_replace('_', ' ', $role));
                                            
                                            // SVGs
                                            $svgIcon = '';
                                            switch($role) {
                                                case 'user_admin':
                                                    // Users (Two men)
                                                    $svgIcon = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" /></svg>';
                                                    break;
                                                case 'coordinador':
                                                    // Compass
                                                    $svgIcon = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6"><circle cx="12" cy="12" r="10" /><path d="M16.24 7.76l-2.12 6.36-6.36 2.12 2.12-6.36 6.36-2.12z" /></svg>';
                                                    break;
                                                case 'tecnico':
                                                    // Tool (Wrench - Custom)
                                                    $svgIcon = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1024 1024" fill="currentColor" class="w-6 h-6"><path d="M161.6 923.2c-15.2 0-30.4-6.4-40.8-17.6-22.4-22.4-22.4-60 0-82.4 11.2-11.2 25.6-17.6 40.8-17.6 15.2 0 30.4 6.4 40.8 17.6 22.4 22.4 22.4 60 0 82.4-10.4 12-24.8 17.6-40.8 17.6z m0-68c-2.4 0-4.8 0.8-6.4 2.4-4 4-4 10.4 0 14.4 1.6 1.6 4 2.4 6.4 2.4 2.4 0 4.8-0.8 6.4-2.4 4-4 4-10.4 0-14.4-1.6-1.6-4-2.4-6.4-2.4z" /><path d="M178.4 972c-24.8 0-47.2-9.6-64.8-27.2l-24-24c-35.2-36-35.2-94.4 0-130.4l1.6-1.6 423.2-362.4c-25.6-43.2-37.6-93.6-33.6-144 4-59.2 28.8-114.4 69.6-156 45.6-46.4 106.4-72 171.2-72 31.2 0 62.4 6.4 91.2 18.4 7.2 3.2 12.8 9.6 14.4 17.6 1.6 8-0.8 16-6.4 21.6L696.8 236.8l84 85.6L904 198.4c4.8-4.8 11.2-7.2 17.6-7.2 1.6 0 3.2 0 4.8 0.8 8 1.6 14.4 7.2 17.6 14.4 18.4 44 23.2 92.8 14.4 140-8.8 48.8-32 92.8-66.4 128-45.6 46.4-105.6 72-169.6 72-35.2 0-70.4-8-101.6-23.2l-377.6 421.6c-17.6 17.6-40.8 27.2-64.8 27.2z m-54.4-147.2c-16 17.6-16 44.8 0.8 61.6l24 24c8 8 18.4 12.8 29.6 12.8 11.2 0 21.6-4.8 29.6-12.8l388.8-434.4c4.8-4.8 11.2-8 18.4-8 4 0 8.8 0.8 12 3.2 28.8 16.8 61.6 25.6 95.2 25.6 51.2 0 98.4-20 134.4-56.8 45.6-47.2 65.6-113.6 52.8-178.4l-112 112.8c-4.8 4.8-11.2 7.2-17.6 7.2-6.4 0-12.8-2.4-17.6-7.2L645.6 253.6c-9.6-9.6-9.6-24.8 0-34.4l112-112.8c-12-2.4-24-3.2-36-3.2-51.2 0-100 20.8-136 57.6-68 68.8-75.2 176.8-18.4 256 7.2 10.4 5.6 24.8-4 32.8l-439.2 375.2z" /><path d="M405.6 522.4c-6.4 0-12.8-2.4-17.6-7.2L216 340h-58.4c-8.8 0-16.8-4.8-20.8-12L57.6 198.4c-5.6-9.6-4-22.4 4-30.4l64-64.8c4.8-4.8 11.2-7.2 17.6-7.2 4.8 0 8.8 1.6 12.8 4l130.4 81.6c7.2 4.8 11.2 12 11.2 20l0.8 58.4 176.8 181.6c4.8 4.8 7.2 11.2 7.2 17.6 0 6.4-2.4 12.8-7.2 16.8-4.8 4.8-10.4 7.2-16.8 7.2s-12.8-2.4-17.6-7.2L256 287.2c-4-4.8-7.2-10.4-7.2-16.8l-0.8-55.2-102.4-64-36.8 37.6 62.4 102.4h54.4c6.4 0 12.8 2.4 17.6 7.2l179.2 182.4c4.8 4.8 7.2 11.2 7.2 17.6 0 6.4-2.4 12.8-7.2 17.6-4 4-10.4 6.4-16.8 6.4zM768.8 979.2c-15.2 0-30.4-6.4-40.8-17.6L520.8 748c-22.4-22.4-22.4-59.2 0-82.4l6.4-6.4-7.2-7.2c-9.6-9.6-9.6-24.8 0.8-34.4 4.8-4.8 10.4-7.2 16.8-7.2s12.8 2.4 17.6 7.2l24 24c9.6 9.6 8.8 24.8 0 34.4l-23.2 24c-4 4-4 10.4 0 14.4L763.2 928c1.6 1.6 4 2.4 6.4 2.4 2.4 0 4.8-0.8 6.4-2.4l94.4-96.8c4-4 4-10.4 0-14.4l-208-213.6c-1.6-1.6-4-2.4-6.4-2.4-2.4 0-4.8 0.8-6.4 2.4L624 629.6c-4.8 4.8-11.2 7.2-17.6 7.2-6.4 0-12.8-2.4-17.6-7.2L568 606.4c-4.8-4.8-7.2-11.2-7.2-17.6 0-6.4 2.4-12.8 7.2-16.8 4.8-4.8 10.4-7.2 16.8-7.2s12.8 2.4 17.6 7.2l4.8 4.8 8-8c11.2-11.2 25.6-17.6 40.8-17.6 15.2 0 30.4 6.4 40.8 17.6L904 782.4c22.4 22.4 22.4 60 0 82.4l-94.4 96.8c-10.4 11.2-24.8 17.6-40.8 17.6z" /></svg>';
                                                    break;
                                                case 'revisor':
                                                    // Document
                                                    $svgIcon = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" /></svg>';
                                                    break;
                                            }
                                        @endphp
                                        <button 
                                            onclick="openRoleModal('{{ $user->id }}', '{{ $role }}', {{ $hasRole ? 'true' : 'false' }}, '{{ $user->nombre }}')"
                                            class="p-2 rounded-full border {{ $hasRole ? 'bg-amber-700 text-white border-amber-700' : 'bg-transparent text-amber-700 border-amber-700 hover:bg-amber-50' }} transition-colors"
                                            title="{{ $roleLabel }}"
                                        >
                                           {!! $svgIcon !!}
                                        </button>
                                    @endforeach
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Confirmation Modal -->
<div id="roleModal" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-50 hidden">
    <div class="bg-white rounded-lg shadow-lg p-6 w-96">
        <h3 class="text-lg font-bold mb-4 text-[#dba800]" id="modalTitle">Confirmar Acción</h3>
        <p class="mb-6 text-gray-700" id="modalMessage">¿Estás seguro?</p>
        <div class="flex justify-end space-x-4">
            <button onclick="closeRoleModal()" class="px-4 py-2 border border-amber-700 text-amber-700 rounded hover:bg-amber-50">Cancelar</button>
            <button id="confirmBtn" class="px-4 py-2 bg-amber-700 text-white rounded hover:bg-[#fbc101] hover:text-[#111] hover:border-[#fbc101] transition-colors">Confirmar</button>
        </div>
    </div>
</div>

<script>
    let currentUserId = null;
    let currentRole = null;

    function openRoleModal(userId, role, hasRole, userName) {
        currentUserId = userId;
        currentRole = role;
        
        const action = hasRole ? 'remover' : 'asignar';
        const roleName = role.replace('_', ' ');
        
        document.getElementById('modalTitle').innerText = `${action.charAt(0).toUpperCase() + action.slice(1)} Rol`;
        document.getElementById('modalMessage').innerText = `¿Estás seguro de que deseas ${action} el rol de ${roleName} a ${userName}?`;
        
        document.getElementById('roleModal').classList.remove('hidden');
    }

    function closeRoleModal() {
        document.getElementById('roleModal').classList.add('hidden');
        currentUserId = null;
        currentRole = null;
    }

    document.getElementById('confirmBtn').addEventListener('click', function() {
        if (!currentUserId || !currentRole) return;

        fetch(`/users/${currentUserId}/role`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ role: currentRole })
        })
        .then(response => response.json())
        .then(data => {
            closeRoleModal();
            // Reload page to reflect changes nicely (or update DOM, but reload is safer for sync)
            window.location.reload(); 
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Ocurrió un error al actualizar el rol.');
            closeRoleModal();
        });
    });
</script>
@endsection
