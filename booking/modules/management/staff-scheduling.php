<?php
require_once dirname(__DIR__, 3) . '/vps_session_fix.php';
require_once '../../config/database.php';
require_once '../../includes/functions.php';
require_once '../../includes/booking-paths.php';

booking_initialize_paths();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'manager') {
    header('Location: ' . booking_base() . 'login.php');
    exit();
}

$page_title = 'Staff Scheduling';

include '../../includes/header-unified.php';
include '../../includes/sidebar-unified.php';
?>

        <main class="lg:ml-64 mt-16 p-4 lg:p-6 flex-1">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl lg:text-3xl font-semibold text-gray-800">Staff Scheduling</h2>
                <div class="text-sm text-gray-600">
                    <i class="fas fa-calendar-day mr-1"></i><?php echo date('l, F j, Y'); ?>
                </div>
            </div>

            <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
                <!-- Today's Schedule -->
                <div class="xl:col-span-1 bg-white rounded-lg shadow p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-800">Today's Schedule</h3>
                        <button id="refreshSchedule" class="text-primary"><i class="fas fa-sync-alt"></i></button>
                    </div>
                    <div id="todaySchedule" class="space-y-3 text-sm">Loading...</div>
                </div>

                <!-- Housekeeping Tasks -->
                <div class="xl:col-span-1 bg-white rounded-lg shadow p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-800">Housekeeping Tasks</h3>
                        <button id="refreshTasks" class="text-primary"><i class="fas fa-sync-alt"></i></button>
                    </div>
                    <div id="tasksList" class="space-y-3 text-sm">Loading...</div>
                </div>

                <!-- Assignment Panel -->
                <div class="xl:col-span-1 bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Assign Task</h3>
                    <form id="assignForm" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Unassigned Task</label>
                            <select id="taskSelect" class="w-full border-gray-300 rounded-md"></select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Assign To (Housekeeping)</label>
                            <select id="staffSelect" class="w-full border-gray-300 rounded-md"></select>
                        </div>
                        <div class="flex justify-end">
                            <button type="submit" class="px-4 py-2 bg-primary text-white rounded-md hover:bg-secondary">
                                <i class="fas fa-user-check mr-2"></i>Assign
                            </button>
                        </div>
                    </form>
                    <div id="assignResult" class="mt-3 text-sm"></div>
                </div>
            </div>
        </main>

        <?php include '../../includes/footer.php'; ?>

        <script>
        document.addEventListener('DOMContentLoaded', ()=>{
            loadSchedule();
            loadTasks();
            loadHousekeepingUsers();
        });

        document.getElementById('refreshSchedule').addEventListener('click', loadSchedule);
        document.getElementById('refreshTasks').addEventListener('click', ()=>{ loadTasks(true); });

        async function loadSchedule(){
            const box = document.getElementById('todaySchedule');
            box.textContent = 'Loading...';
            try{
                const res = await fetch('../../api/get-today-schedule.php');
                const json = await res.json();
                if(!json.success) throw new Error(json.message||'Failed');
                if(!json.schedule || json.schedule.length===0){ box.textContent='No scheduled activities.'; return; }
                box.innerHTML = json.schedule.map(item => `
                    <div class="p-3 border rounded flex items-start justify-between">
                        <div>
                            <div class="font-medium">${item.title}</div>
                            <div class="text-gray-500 text-xs">${item.description}</div>
                        </div>
                        <div class="text-right text-xs text-gray-600">
                            <div><i class="far fa-clock mr-1"></i>${item.time}</div>
                            <div>Room ${item.room_number||'-'}</div>
                        </div>
                    </div>
                `).join('');
            }catch(e){ box.textContent = 'Error loading schedule'; }
        }

        async function loadTasks(fillSelect=false){
            const list = document.getElementById('tasksList');
            list.textContent = 'Loading...';
            try{
                const res = await fetch('../../api/get-recent-housekeeping-tasks.php');
                const json = await res.json();
                if(!json.success){ throw new Error(); }
                const tasks = json.tasks || json.data || [];
                if(tasks.length===0){ list.textContent='No tasks.'; }
                else {
                    list.innerHTML = tasks.map(t => `
                        <div class="p-3 border rounded flex items-center justify-between ${t.status==='completed'?'bg-green-50':''}">
                            <div>
                                <div class="font-medium">Task #${t.id} · ${t.task_type?.replace('_',' ')}</div>
                                <div class="text-xs text-gray-500">Room ${t.room_number||t.room_id} · Status: ${t.status}</div>
                            </div>
                            <div class="text-xs text-gray-600">${t.assigned_to_name?('Assigned to '+t.assigned_to_name):'Unassigned'}</div>
                        </div>
                    `).join('');
                }

                // Populate unassigned in select
                const unassigned = tasks.filter(t => !t.assigned_to_name && (!t.assigned_to || t.assigned_to===null));
                const sel = document.getElementById('taskSelect');
                sel.innerHTML = unassigned.length? unassigned.map(t=>`<option value="${t.id}">Task #${t.id} · Room ${t.room_number||t.room_id} · ${t.task_type}</option>`).join('') : '<option value="">No unassigned tasks</option>';
            }catch(e){ list.textContent='Error loading tasks'; }
        }

        async function loadHousekeepingUsers(){
            try{
                const res = await fetch('../../api/get-users.php?role=housekeeping&status=active');
                const json = await res.json();
                const users = json.users || [];
                const sel = document.getElementById('staffSelect');
                sel.innerHTML = users.length? users.map(u=>`<option value="${u.id}">${u.name}</option>`).join('') : '<option value="">No housekeeping users</option>';
            }catch(e){
                document.getElementById('staffSelect').innerHTML = '<option value="">Error loading users</option>';
            }
        }

        document.getElementById('assignForm').addEventListener('submit', async (e)=>{
            e.preventDefault();
            const taskId = document.getElementById('taskSelect').value;
            const userId = document.getElementById('staffSelect').value;
            const box = document.getElementById('assignResult');
            box.textContent = 'Assigning...'; box.className='mt-3 text-sm text-gray-600';
            try{
                const res = await fetch('../../api/assign-housekeeping-task.php',{
                    method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({task_id: taskId, user_id: userId})
                });
                const json = await res.json();
                if(json.success){
                    box.className='mt-3 text-sm text-green-600';
                    box.textContent = json.message || 'Assigned';
                    loadTasks();
                }else{
                    box.className='mt-3 text-sm text-red-600';
                    box.textContent = json.message || 'Failed';
                }
            }catch(e){ box.className='mt-3 text-sm text-red-600'; box.textContent='Error assigning task'; }
        });
        </script>
<?php // end file ?>

