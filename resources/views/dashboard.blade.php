<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exam Result Processing System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-gray-50 text-gray-800 font-sans antialiased" x-data="appData()">

    <!-- Navigation -->
    <nav class="bg-indigo-600 text-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <svg class="w-8 h-8 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                    <span class="font-bold text-xl">Exam Result System</span>
                </div>
                <div class="flex items-center" x-show="token" x-cloak>
                    <button @click="logout" class="bg-indigo-700 hover:bg-indigo-800 px-4 py-2 rounded-md text-sm font-medium transition duration-150">Logout</button>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        <!-- Alerts -->
        <div x-show="error" x-cloak class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline" x-text="error"></span>
            <span class="absolute top-0 bottom-0 right-0 px-4 py-3" @click="error = ''">
                <svg class="fill-current h-6 w-6 text-red-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><title>Close</title><path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/></svg>
            </span>
        </div>
        <div x-show="success" x-cloak class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline" x-text="success"></span>
        </div>

        <!-- Login Section -->
        <div x-show="!token" x-cloak class="max-w-md mx-auto bg-white rounded-lg shadow-md overflow-hidden mt-10">
            <div class="px-6 py-8">
                <h2 class="text-2xl font-bold text-center text-gray-700 mb-8">Admin Login</h2>
                <form @submit.prevent="login">
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="email">Email</label>
                        <input x-model="loginForm.email" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-indigo-500" id="email" type="email" required>
                    </div>
                    <div class="mb-6">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="password">Password</label>
                        <input x-model="loginForm.password" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 mb-3 leading-tight focus:outline-none focus:ring-2 focus:ring-indigo-500" id="password" type="password" required>
                    </div>
                    <div class="flex items-center justify-between">
                        <button :disabled="loading" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline w-full transition duration-150 disabled:opacity-50" type="submit">
                            <span x-show="!loading">Sign In</span>
                            <span x-show="loading">Authenticating...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Dashboard Section -->
        <div x-show="token" x-cloak class="space-y-6">
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Upload Section (Bulk) -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold border-b pb-2 mb-4">Bulk Upload Marks (CSV)</h3>
                    <p class="text-sm text-gray-600 mb-4">Upload the sample_marks.csv file to process student results asynchronously via Redis queues.</p>
                    
                    <form @submit.prevent="uploadCsv" class="space-y-4">
                        <div class="flex items-center justify-center w-full">
                            <label for="dropzone-file" class="flex flex-col items-center justify-center w-full h-32 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 hover:bg-gray-100 transition duration-150">
                                <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                    <svg class="w-8 h-8 mb-3 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                                    <p class="mb-2 text-sm text-gray-500"><span class="font-semibold" x-text="file ? file.name : 'Click to upload'"></span></p>
                                    <p class="text-xs text-gray-500" x-show="!file">CSV files only</p>
                                </div>
                                <input id="dropzone-file" type="file" class="hidden" accept=".csv" @change="file = $event.target.files[0]" />
                            </label>
                        </div>
                        <button :disabled="!file || loading" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded focus:outline-none transition duration-150 disabled:opacity-50 flex justify-center">
                            <span x-show="!loading && !uploading">Upload & Process</span>
                            <span x-show="uploading">
                                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                Uploading...
                            </span>
                        </button>
                    </form>
                </div>

                <!-- Single Entry Section -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold border-b pb-2 mb-4">Manual Entry (Correction)</h3>
                    <p class="text-sm text-gray-600 mb-4">Update or enter a mark for a single student. Results are re-computed automatically in the background.</p>
                    
                    <form @submit.prevent="submitSingleMark" class="space-y-4">
                        <div class="grid grid-cols-3 gap-4">
                            <div>
                                <label class="block text-gray-700 text-xs font-bold mb-1">Roll Number</label>
                                <input type="text" x-model="singleMark.roll_number" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                                <p class="text-xs text-gray-400 mt-1">e.g. CSE2026006</p>
                            </div>
                            <div>
                                <label class="block text-gray-700 text-xs font-bold mb-1">Course Code</label>
                                <input type="text" x-model="singleMark.course_code" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                                <p class="text-xs text-gray-400 mt-1">e.g. CS302</p>
                            </div>
                            <div>
                                <label class="block text-gray-700 text-xs font-bold mb-1">Component Name</label>
                                <select x-model="singleMark.component_name" class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                                    <option value="" disabled selected>Select...</option>
                                    <option value="Internal">Internal</option>
                                    <option value="Final">Final</option>
                                </select>
                            </div>
                        </div>
                        <div>
                            <label class="block text-gray-700 text-xs font-bold mb-1">Marks Obtained</label>
                            <input type="number" step="0.5" x-model.number="singleMark.marks_obtained" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                        </div>
                        <button :disabled="loading" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none transition duration-150 disabled:opacity-50 mt-2">
                            <span>Submit Mark</span>
                        </button>
                    </form>
                </div>
            </div>

            <!-- Batch Status Section -->
            <div class="bg-white rounded-lg shadow p-6" x-show="activeBatch" x-cloak>
                <div class="flex justify-between items-center border-b pb-2 mb-4">
                    <h3 class="text-lg font-semibold">Queue Processing Status (Bulk Upload)</h3>
                    <span class="px-2 py-1 text-xs font-semibold rounded-full" 
                        :class="{
                            'bg-yellow-100 text-yellow-800': activeBatch?.status === 'processing',
                            'bg-green-100 text-green-800': activeBatch?.status === 'completed',
                            'bg-red-100 text-red-800': activeBatch?.status === 'failed'
                        }" x-text="activeBatch?.status?.toUpperCase()">
                    </span>
                </div>
                
                <div class="space-y-4">
                    <div class="flex justify-between text-sm text-gray-600">
                        <span>Batch ID: <span class="font-mono" x-text="activeBatch?.id"></span></span>
                        <span>Processed: <span x-text="activeBatch?.processed_rows || 0"></span> / <span x-text="activeBatch?.total_rows || 0"></span> rows</span>
                    </div>
                    
                    <!-- Progress Bar -->
                    <div class="w-full bg-gray-200 rounded-full h-2.5">
                        <div class="bg-indigo-600 h-2.5 rounded-full transition-all duration-500" :style="`width: ${Math.min(100, Math.round(((activeBatch?.processed_rows || 0) / (activeBatch?.total_rows || 1)) * 100))}%`"></div>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4 text-center pt-2">
                        <div class="bg-gray-50 rounded p-2">
                            <p class="text-xs text-gray-500 uppercase">Errors</p>
                            <p class="text-xl font-semibold text-red-600" x-text="activeBatch?.failed_rows || 0"></p>
                        </div>
                        <div class="bg-gray-50 rounded p-2">
                            <p class="text-xs text-gray-500 uppercase">Completion</p>
                            <p class="text-xl font-semibold text-indigo-600" x-text="Math.round(((activeBatch?.processed_rows || 0) / (activeBatch?.total_rows || 1)) * 100) + '%'"></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Results Section -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="p-6 border-b flex justify-between items-center bg-gray-50">
                    <h3 class="text-lg font-semibold text-gray-800">Computed Results (Read Replica)</h3>
                    <div class="flex space-x-2">
                        <button @click="computeResults" :disabled="computing" class="bg-green-600 hover:bg-green-700 text-white text-sm font-medium py-1 px-3 rounded flex items-center transition duration-150 disabled:opacity-50">
                            <span x-show="!computing">Compute Grades</span>
                            <span x-show="computing">Computing...</span>
                        </button>
                        <button @click="fetchResults" :disabled="refreshing" class="text-indigo-600 hover:text-indigo-900 text-sm font-medium flex items-center disabled:opacity-50 transition duration-150">
                            <svg :class="{'animate-spin': refreshing}" class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                            <span x-show="!refreshing">Refresh Data</span>
                            <span x-show="refreshing">Refreshing...</span>
                        </button>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" x-show="results.length > 0">
                    <template x-for="group in groupedResults" :key="group.student.roll_number">
                        <div class="bg-white border rounded-lg shadow-sm overflow-hidden flex flex-col">
                            <div class="bg-indigo-50 border-b border-indigo-100 px-4 py-3 flex justify-between items-center">
                                <div>
                                    <h4 class="font-bold text-indigo-900" x-text="group.student.name"></h4>
                                    <p class="text-xs text-indigo-700" x-text="group.student.roll_number + ' • ' + group.student.programme.code"></p>
                                </div>
                            </div>
                            <div class="p-0 flex-1">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Course</th>
                                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Marks</th>
                                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Grade</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 bg-white">
                                        <template x-for="result in group.courses" :key="result.id">
                                            <tr class="hover:bg-gray-50">
                                                <td class="px-4 py-3 text-sm">
                                                    <div class="font-medium text-gray-900" x-text="result.course.code"></div>
                                                    <div class="text-xs text-gray-500 truncate" style="max-width: 140px;" x-text="result.course.name"></div>
                                                </td>
                                                <td class="px-4 py-3 text-sm text-right font-semibold text-gray-900" x-text="result.total_marks || '-'"></td>
                                                <td class="px-4 py-3 text-sm text-right">
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                                        :class="{
                                                            'bg-green-100 text-green-800': ['A+', 'A', 'B'].includes(result.grade),
                                                            'bg-yellow-100 text-yellow-800': ['C', 'D'].includes(result.grade),
                                                            'bg-red-100 text-red-800': result.grade === 'E' || result.grade === 'F'
                                                        }" x-text="result.grade || '-'">
                                                    </span>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </template>
                </div>
                
                <div x-show="results.length === 0" class="text-center py-10 bg-white rounded-lg shadow-sm border border-gray-200">
                    <p class="text-gray-500 text-sm">No results found. Upload marks to compute grades.</p>
                </div>
            </div>
            
        </div>
    </div>

    <script>
        function appData() {
            return {
                token: localStorage.getItem('auth_token') || '',
                loading: false,
                refreshing: false,
                error: '',
                success: '',
                loginForm: { email: 'admin@exam.edu', password: 'password' },
                singleMark: { roll_number: '', course_code: '', component_name: '', marks_obtained: '' },
                file: null,
                activeBatch: null,
                uploading: false,
                computing: false,
                pollInterval: null,
                results: [],

                get groupedResults() {
                    const groups = {};
                    this.results.forEach(r => {
                        if (!groups[r.student.roll_number]) {
                            groups[r.student.roll_number] = {
                                student: r.student,
                                courses: []
                            };
                        }
                        groups[r.student.roll_number].courses.push(r);
                    });
                    return Object.values(groups);
                },

                init() {
                    if (this.token) {
                        this.fetchResults();
                    }
                },

                async api(endpoint, options = {}) {
                    const headers = {
                        'Accept': 'application/json',
                        ...options.headers
                    };
                    
                    if (this.token) {
                        headers['Authorization'] = `Bearer ${this.token}`;
                    }
                    if (!(options.body instanceof FormData)) {
                        headers['Content-Type'] = 'application/json';
                        if (options.body) options.body = JSON.stringify(options.body);
                    }

                    const response = await fetch(`/api${endpoint}`, { ...options, headers });
                    const data = await response.json();
                    
                    if (!response.ok) {
                        if (response.status === 401) this.logout();
                        
                        // Handle validation errors (Laravel 422 responses)
                        if (response.status === 422 && data.errors) {
                            const firstError = Object.values(data.errors)[0][0];
                            throw new Error(firstError);
                        }
                        
                        throw new Error(data.message || data.error || 'API Error');
                    }
                    return data;
                },

                async login() {
                    this.loading = true;
                    this.error = '';
                    try {
                        const data = await this.api('/auth/login', {
                            method: 'POST',
                            body: this.loginForm
                        });
                        this.token = data.token;
                        localStorage.setItem('auth_token', this.token);
                        this.success = 'Successfully logged in!';
                        setTimeout(() => this.success = '', 3000);
                        this.fetchResults();
                    } catch (err) {
                        this.error = err.message;
                    } finally {
                        this.loading = false;
                    }
                },

                logout() {
                    this.token = '';
                    localStorage.removeItem('auth_token');
                    this.results = [];
                    this.activeBatch = null;
                    if (this.pollInterval) clearInterval(this.pollInterval);
                },

                async uploadCsv() {
                    if (!this.file) return;
                    this.loading = true;
                    this.uploading = true;
                    this.error = '';
                    this.success = '';
                    
                    const formData = new FormData();
                    formData.append('file', this.file);
                    
                    try {
                        const data = await this.api('/examinations/1/marks/upload', {
                            method: 'POST',
                            headers: {
                                'X-Idempotency-Key': 'upload-' + Date.now()
                            },
                            body: formData
                        });
                        
                        this.activeBatch = { 
                            id: data.batch_id, 
                            status: data.status, 
                            total_rows: data.total_rows, 
                            processed_rows: 0 
                        };
                        this.success = 'File uploaded successfully! Processing started in the background.';
                        setTimeout(() => this.success = '', 3000);
                        this.file = null;
                        this.startPolling();
                    } catch (err) {
                        this.error = err.message;
                    } finally {
                        this.loading = false;
                        this.uploading = false;
                    }
                },

                async submitSingleMark() {
                    this.loading = true;
                    this.error = '';
                    this.success = '';
                    
                    try {
                        await this.api('/marks', {
                            method: 'POST',
                            body: this.singleMark
                        });
                        
                        this.success = 'Mark recorded successfully! The result is being re-computed in the background.';
                        this.singleMark.marks_obtained = ''; // reset just the marks
                        setTimeout(() => {
                            this.success = '';
                            this.fetchResults(); // Refresh after a small delay to let queue run
                        }, 2000);
                    } catch (err) {
                        this.error = err.message;
                    } finally {
                        this.loading = false;
                    }
                },

                async fetchResults() {
                    this.refreshing = true;
                    try {
                        const data = await this.api('/results');
                        this.results = data.data;
                    } catch (err) {
                        console.error('Failed to fetch results', err);
                    } finally {
                        setTimeout(() => this.refreshing = false, 500); // 500ms debounce
                    }
                },

                async computeResults() {
                    this.computing = true;
                    this.error = '';
                    this.success = '';
                    
                    try {
                        // Assuming examination ID is 1 for demo purposes
                        await this.api('/examinations/1/results/compute', {
                            method: 'POST'
                        });
                        this.success = 'Grade computation started in the background!';
                        setTimeout(() => this.success = '', 3000);
                        
                        // Wait a bit for the queue to process, then refresh
                        setTimeout(() => this.fetchResults(), 2000);
                    } catch (err) {
                        this.error = err.message;
                    } finally {
                        this.computing = false;
                    }
                },

                startPolling() {
                    if (this.pollInterval) clearInterval(this.pollInterval);
                    
                    this.pollInterval = setInterval(async () => {
                        if (!this.activeBatch) return clearInterval(this.pollInterval);
                        
                        try {
                            const data = await this.api(`/mark-uploads/${this.activeBatch.id}`);
                            this.activeBatch = data;
                            
                            // Refresh results while processing to show real-time updates
                            this.fetchResults();
                            
                            if (this.activeBatch.status === 'completed' || this.activeBatch.status === 'failed') {
                                clearInterval(this.pollInterval);
                                this.fetchResults(); // Final fetch
                            }
                        } catch (err) {
                            console.error('Polling error', err);
                            clearInterval(this.pollInterval);
                        }
                    }, 2000);
                }
            }
        }
    </script>
</body>
</html>
