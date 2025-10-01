        </main>
    </div>

    <!-- JavaScript for additional functionality -->
    <script>

        // Update current date and time
        function updateDateTime() {
            const now = new Date();
            const dateOptions = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
            const timeOptions = { hour: '2-digit', minute: '2-digit', second: '2-digit' };
            
            const dateElement = document.getElementById('current-date');
            const timeElement = document.getElementById('current-time');
            
            if (dateElement) {
                dateElement.textContent = now.toLocaleDateString('en-US', dateOptions);
            }
            if (timeElement) {
                timeElement.textContent = now.toLocaleTimeString('en-US', timeOptions);
            }
        }
        
        // Initialize date/time and update every second
        updateDateTime();
        setInterval(updateDateTime, 1000);

        // Close sidebar on window resize
        window.addEventListener('resize', function() {
            if (window.innerWidth >= 1024) {
                const sidebar = document.getElementById('sidebar');
                sidebar.style.transform = 'translateX(0)';
            }
        });

        // Demo mode specific functionality
        <?php if ($is_demo_mode): ?>
        // Show demo mode notifications
        function showDemoNotification(message, type = 'info') {
            const notification = document.createElement('div');
            notification.className = `fixed top-20 right-4 p-4 rounded-lg shadow-lg z-50 ${
                type === 'info' ? 'bg-blue-500 text-white' : 
                type === 'success' ? 'bg-green-500 text-white' : 
                type === 'warning' ? 'bg-yellow-500 text-white' : 
                'bg-red-500 text-white'
            }`;
            notification.innerHTML = `
                <div class="flex items-center">
                    <i class="fas fa-graduation-cap mr-2"></i>
                    <span>${message}</span>
                </div>
            `;
            
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.remove();
            }, 3000);
        }

        // Demo mode welcome message
        document.addEventListener('DOMContentLoaded', function() {
            showDemoNotification('Welcome to POS Simulation Mode! Practice freely with demo data.', 'success');
        });
        <?php endif; ?>
    </script>
</body>
</html>
