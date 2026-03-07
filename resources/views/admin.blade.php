@extends('template')
@section('title', 'Admin - SimplyCMS')
@section('content')
<div class="flex flex-col w-full max-w-6xl mx-auto m-10 p-5 bg-[#3d3d3d] rounded-3xl shadow-2xl text-gray-200">
    <div class="flex justify-between items-center border-b border-gray-600 pb-5 mb-5">
        <h1 class="text-3xl font-bold text-white">Admin Dashboard</h1>
        <a href="{{ url('/') }}" class="bg-orange-400 hover:bg-green-800 transition px-4 py-2 rounded-full font-bold text-white flex items-center gap-2">
            <span class="material-icons text-sm">arrow_back</span> Back to Home
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
        <div class="bg-[#2d2d2d] p-6 rounded-2xl shadow-lg text-center">
            <h3 class="text-xl font-semibold">Total Posts</h3>
            <p class="text-4xl font-bold mt-2 text-orange-400">{{ count($posts ?? []) }}</p>
        </div>
        <div class="p-6 text-center">
            <h3 class="text-xl font-semibold">Hello There</h3>
            <p class="text-4xl font-bold mt-2 text-green-500">{{ Auth::user()->username }}</p>
        </div>
        <div class="bg-[#2d2d2d] p-6 rounded-2xl shadow-lg text-center">
            <h3 class="text-xl font-semibold">Storage Used (Cloudflare R2)</h3>
            <p class="text-4xl font-bold mt-2 text-blue-400">{{ $storageSize }}</p>
        </div>
    </div>

    <div class="bg-[#2d2d2d] rounded-2xl overflow-hidden">
        <div class="p-2 border-b border-gray-700 bg-[#1d1d1d]">
            <nav class="flex gap-4 px-4">
                <button id="postManagement" class="py-3 px-4 text-gray-400 hover:text-white hover:border-b-2 transition" onclick="showPosts()">
                    Posts Management
                </button>
                <button id="usersManagement" class="py-3 px-4 text-gray-400 hover:text-white hover:border-b-2 transition" onclick="showUsers()">
                    User Management
                </button>
                <button id="r2Management" class="py-3 px-4 text-gray-400 hover:text-white hover:border-b-2 transition" onclick="showR2Usage()">
                    R2 Usage
                </button>
            </nav>
        </div>
        <!-- table content -->
        <div id="loading" class="rounded-3xl p-8 flex flex-col justify-center w-[100%] mx-auto hidden">
            <div id="postLoading" class="flex flex-col items-center gap-3 p-5">
                <div class="w-8 h-8 border-4 border-orange-400 border-t-transparent rounded-full animate-spin"></div>
                <span class="text-gray-400 font-semibold">Fetching Data...</span>
            </div>
        </div>
        <div id="tableContent" class="overflow-y-auto max-w-[100%] mx-auto hidden">
            <table class="w-full text-left">
                <thead id="tableHead" class="bg-[#1d1d1d] text-gray-400 uppercase text-xs hidden">
                    <tr>
                        <!-- head -->
                    </tr>
                </thead>
                <tbody id="tableBody" class="divide-y divide-gray-700">
                    <!-- body -->
                </tbody>
            </table>
            <!-- pagination -->
            <div id="pagination" class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between p-5">
                <div id="pageIndex">
                    <!-- page index -->
                </div>
                <div id="indexControl" class="hidden">
                    <!-- index control -->
                </div>
            </div>
        </div>
    </div>
</div>
<script>
const postManagement = document.getElementById('postManagement');
const usersManagement = document.getElementById('usersManagement');
const r2Management = document.getElementById('r2Management');
const table = document.getElementById('tableContent');
const loading = document.getElementById('loading');
const pageIndex = document.getElementById('pageIndex');
const indexControl = document.getElementById('indexControl');
const pagination = document.getElementById('pagination');
const tableHead = document.getElementById('tableHead');
const tableBody = document.getElementById('tableBody');

function showPosts(offset = 0) {
    // Safely check if event exists before trying to prevent default
    if (typeof event !== 'undefined') {
        event.preventDefault();
    }

    if (table.classList.contains('hidden')) {
        table.classList.remove('hidden');
    } else {
        table.classList.add('hidden');
    }

    if (usersManagement.classList.contains('border-b-2')) {
        usersManagement.classList.remove('border-b-2');
        usersManagement.classList.remove('text-orange-400');
        usersManagement.classList.add('text-gray-400');
    }

    if (r2Management.classList.contains('border-b-2')) {
        r2Management.classList.remove('border-b-2');
        r2Management.classList.remove('text-orange-400');
        r2Management.classList.add('text-gray-400');
    }
    
    postManagement.classList.add('border-b-2');
    postManagement.classList.add('text-orange-400');
    postManagement.classList.remove('text-gray-400');
    loading.classList.remove('hidden');
    
    // Always declare variables with const or let
    const limit = 5; 
    
    fetch(`{{ url('/posts') }}?offset=${offset}`, {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        table.classList.remove('hidden');
        loading.classList.add('hidden');
        pagination.classList.remove('hidden');
        indexControl.classList.remove('hidden');
        pageIndex.classList.remove('hidden');
        
        // Explicitly parse the offset as an integer just to be extra safe
        const currentOffset = parseInt(data.offset, 10);
        const currentPage = Math.floor(currentOffset / limit) + 1;
        
        pageIndex.innerHTML = `
            <span class="text-gray-400">Showing page ${currentPage}</span>
        `;

        indexControl.innerHTML = `
            <div class="flex gap-2">
                <button class="px-3 py-1 bg-gray-700 rounded hover:bg-orange-400 disabled:opacity-50" 
                    ${currentOffset === 0 ? 'disabled' : ''} 
                    onclick="showPosts(${Math.max(0, currentOffset - limit)})">Previous</button>
                    
                <button class="px-3 py-1 bg-gray-700 rounded hover:bg-orange-400 disabled:opacity-50" 
                    ${data.posts.length < limit ? 'disabled' : ''} 
                    onclick="showPosts(${currentOffset + limit})">Next</button>
            </div>
        `;

        tableHead.classList.remove('hidden');
        tableHead.innerHTML = `
            <tr class="bg-[#1d1d1d] text-gray-400 uppercase text-xs">
                <th id="col1head" class="p-4">Post ID</th>
                <th id="col2head" class="p-4">User ID</th>
                <th id="col3head" class="p-4">Display Name</th>
                <th id="col4head" class="p-4">Title</th>
                <th id="col5head" class="p-4">Contents</th>
                <th id="col6head" class="p-4">Image</th>
                <th id="col7head" class="p-4 text-center">Actions</th>
            </tr>
        `;
        
        tableBody.innerHTML = '';
        data.posts.forEach((post) => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td class="p-4">${post.post_id}</td>
                <td class="p-4">${post.user_id}</td>
                <td class="p-4">${post.display_name}</td>
                <td class="p-4">${post.title}</td>
                <td class="p-4">
                    <div class="max-w-[500px] h-[80px] overflow-y-auto whitespace-normal break-words">
                        ${post.contents}
                    </div>
                </td>
                <td class="p-4">
                    <div>
                        <img src="${post.file_path}" alt="Post Image" class="max-h-[50px] max-w-[50px]">
                    </div>
                </td>
                <td class="p-4 text-center">
                    <button class="flex justify-center items-center bg-red-600 hover:bg-red-800 text-white font-bold py-2 px-4 rounded" onclick="deletePost(${post.post_id})">
                        <span class="material-icons">delete</span>
                    </button>
                </td>
            `;
            tableBody.appendChild(row);
        });
    })
    .catch(error => {
        console.error('Error:', error);
    });
}
function showUsers(offset = 0) {
    if (typeof event !== 'undefined') {
        event.preventDefault();
    }

    if (table.classList.contains('hidden')) {
        table.classList.remove('hidden');
    } else {
        table.classList.add('hidden');
    }
    
    if (postManagement.classList.contains('border-b-2')) {
        postManagement.classList.remove('border-b-2');
        postManagement.classList.remove('text-orange-400');
        postManagement.classList.add('text-gray-400');
    }

    if (r2Management.classList.contains('border-b-2')) {
        r2Management.classList.remove('border-b-2');
        r2Management.classList.remove('text-orange-400');
        r2Management.classList.add('text-gray-400');
    }

    usersManagement.classList.add('border-b-2');
    usersManagement.classList.add('text-orange-400');
    usersManagement.classList.remove('text-gray-400');
    loading.classList.remove('hidden');
    
    const limit = 5; 
    
    fetch(`{{ url('/users') }}?offset=${offset}`, {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        loading.classList.add('hidden');
        pagination.classList.remove('hidden');
        indexControl.classList.remove('hidden');
        pageIndex.classList.remove('hidden');
        table.classList.remove('hidden');
        
        const currentOffset = parseInt(data.offset, 10);
        const currentPage = Math.floor(currentOffset / limit) + 1;
        
        pageIndex.innerHTML = `
            <span class="text-gray-400">Showing page ${currentPage}</span>
        `;

        indexControl.innerHTML = `
            <div class="flex gap-2">
                <button class="px-3 py-1 bg-gray-700 rounded hover:bg-orange-400 disabled:opacity-50" 
                    ${currentOffset === 0 ? 'disabled' : ''} 
                    onclick="showUsers(${Math.max(0, currentOffset - limit)})">Previous</button>
                    
                <button class="px-3 py-1 bg-gray-700 rounded hover:bg-orange-400 disabled:opacity-50" 
                    ${data.users.length < limit || data.users.length === 5 ? 'disabled' : ''}
                    onclick="showUsers(${currentOffset + limit})">Next</button>
            </div>
        `;

        tableHead.classList.remove('hidden');
        tableHead.innerHTML = `
            <tr class="bg-[#1d1d1d] text-gray-400 uppercase text-xs">
                <th class="p-4">User ID</th>
                <th class="p-4">Username</th>
                <th class="p-4">Email</th>
                <th class="p-4">Post Count</th>
                <th class="p-4">Hashed Password</th>
                <th class="p-4">Access</th>
                <th class="p-4 text-center">Actions</th>
            </tr>
        `;
        
        tableBody.innerHTML = '';
        data.users.forEach((user, index) => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td class="p-4">${user.user_id}</td>
                <td class="p-4">${user.username}</td>
                <td class="p-4">${user.email}</td>
                <td class="p-4">${user.remember_token != null ? "TRUE" : "FALSE"}</td>
                <td class="p-4">
                    <div class="max-w-[150px] h-[80px] overflow-y-auto whitespace-normal break-words">
                        ${user.password}
                    </div>
                </td>
                <td class="p-4">
                    <span class="px-2 py-1 rounded text-xs font-bold ${user.access === 'admin' ? 'bg-purple-600' : (user.access === 'user' ? 'bg-orange-400' : 'bg-gray-600')}">
                        ${user.access}
                    </span>
                </td>
                <td class="p-4 text-center">
                    <button class="bg-blue-600 hover:bg-blue-800 text-white font-bold py-1 px-3 rounded text-xs">
                        Edit
                    </button>
                </td>
            `;
            tableBody.appendChild(row);
        });
    })
    .catch(error => {
        console.error('Error:', error);
    });
}

function showR2Usage() {
    if (typeof event !== 'undefined') {
        event.preventDefault();
    }

    table.classList.add('hidden');
    loading.classList.remove('hidden');
    pagination.classList.add('hidden');

    [postManagement, usersManagement].forEach(el => {
        el.classList.remove('border-b-2', 'text-orange-400');
        el.classList.add('text-gray-400');
    });

    r2Management.classList.add('border-b-2', 'text-orange-400');
    r2Management.classList.remove('text-gray-400');

    fetch(`{{ url('/r2-usage') }}`, {
        method: 'GET',
        headers: { 'Content-Type': 'application/json' }
    })
    .then(response => response.json())
    .then(data => {
        loading.classList.add('hidden');
        table.classList.remove('hidden');
        table.innerHTML = `
            <h1 class="text-3xl text-gray-400">${Math.floor(data.payloadSize / 1024 / 1024)} MB</h1>
            <h1 class="text-3xl text-gray-400">${data.objectCount - 1} Objects</h1>
        `
    })
    .catch(error => {
        console.error('Error:', error);
        loading.classList.add('hidden');
    });
}
function deletePost(postId) {
    if (!confirm('Are you sure you want to delete this post?')) return;
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ url("/delete-post") }}';
    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = '_token';
    csrfInput.value = '{{ csrf_token() }}';
    const methodInput = document.createElement('input');
    methodInput.type = 'hidden';
    methodInput.name = '_method';
    methodInput.value = 'DELETE';
    const idInput = document.createElement('input');
    idInput.type = 'hidden';
    idInput.name = 'id';
    idInput.value = postId;
    form.appendChild(csrfInput);
    form.appendChild(methodInput);
    form.appendChild(idInput);
    document.body.appendChild(form);
    form.submit();
}
</script>
@endsection