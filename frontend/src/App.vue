<template>
  <div class="app-container">
    <h1>My Laravel + Vue<br>Task Manager</h1>

    <!-- Login Form (Shows if not logged in) -->
    <div v-if="!token" class="card">
      <h2>Login</h2>
      <form @submit.prevent="handleLogin">
        <div class="form-group">
          <input type="email" v-model="email" placeholder="Email" required />
        </div>
        <div class="form-group">
          <input type="password" v-model="password" placeholder="Password" required />
        </div>
        <button type="submit" class="btn-primary">Login</button>
      </form>
      <p v-if="errorMessage" class="error-text">{{ errorMessage }}</p>
    </div>

    <!-- Authenticated Dashboard -->
    <div v-else>
      <div class="header-actions">
        <button @click="handleLogout" class="btn-logout">Logout</button>
      </div>

      <!-- Create Task Card -->
      <div class="card">
        <h2>Create New Task</h2>
        <form @submit.prevent="createTask">
          <div class="form-group">
            <input type="text" v-model="newTask.title" placeholder="Task Title" required />
          </div>
          <div class="form-group">
            <textarea v-model="newTask.description" placeholder="Description" rows="3"></textarea>
          </div>
          <button type="submit" class="btn-success">Add Task</button>
        </form>
      </div>

      <!-- Tasks Display List -->
      <div class="tasks-section">
        <h2>Your Tasks</h2>
        <div v-if="tasks.length === 0" class="no-tasks">No tasks found. Add some above!</div>
        <div v-else class="tasks-list">
          <div v-for="task in tasks" :key="task.id" class="task-card">
            <div class="task-info">
              <h3>{{ task.title }}</h3>
              <p>{{ task.description }}</p>
            </div>
            <!-- Delete Button Element -->
            <button @click="deleteTask(task.id)" class="btn-delete">
              Delete
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

// State Variables
const email = ref('');
const password = ref('');
const token = ref(localStorage.getItem('token') || '');
const tasks = ref([]);
const errorMessage = ref('');

const newTask = ref({
  title: '',
  description: ''
});

// Fetch all tasks for the logged-in user
const fetchTasks = async () => {
  if (!token.value) return;
  try {
    const response = await axios.get('http://127.0.0.1:8000/api/tasks', {
      headers: { Authorization: `Bearer ${token.value}` }
    });
    tasks.value = response.data;
  } catch (error) {
    console.error('Error fetching tasks:', error);
  }
};

// Handle Authentication Login
const handleLogin = async () => {
  try {
    errorMessage.value = '';
    const response = await axios.post('http://127.0.0.1:8000/api/login', {
      email: email.value,
      password: password.value
    });
    
    token.value = response.data.token;
    localStorage.setItem('token', token.value);
    
    // Clear credentials fields and grab tasks
    email.value = '';
    password.value = '';
    fetchTasks();
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'The provided credentials do not match our records.';
  }
};

// Handle Authentication Logout
const handleLogout = () => {
  token.value = '';
  tasks.value = [];
  localStorage.removeItem('token');
};

// Create a new task via API
const createTask = async () => {
  try {
    const response = await axios.post('http://127.0.0.1:8000/api/tasks', newTask.value, {
      headers: { Authorization: `Bearer ${token.value}` }
    });
    tasks.value.push(response.data);
    
    // Reset form fields
    newTask.value.title = '';
    newTask.value.description = '';
  } catch (error) {
    console.error('Error creating task:', error);
  }
};

// Delete a task via API
const deleteTask = async (id) => {
  if (!confirm('Are you sure you want to delete this task?')) return;
  
  try {
    await axios.delete(`http://127.0.0.1:8000/api/tasks/${id}`, {
      headers: { Authorization: `Bearer ${token.value}` }
    });
    
    // Remove from UI array smoothly
    tasks.value = tasks.value.filter(task => task.id !== id);
  } catch (error) {
    console.error('Error deleting task:', error);
  }
};

// Load existing tasks if token exists on boot
onMounted(() => {
  if (token.value) {
    fetchTasks();
  }
});
</script>

<style scoped>
/* App Layout Container */
.app-container {
  max-width: 600px;
  margin: 40px auto;
  padding: 0 20px;
  font-family: Arial, sans-serif;
  color: #fff;
}

/* Header Adjustments to prevent overlaps */
h1 {
  text-align: center;
  font-size: 2.5rem;
  line-height: 1.2;
  margin-bottom: 35px;
  font-weight: 700;
}

h2 {
  text-align: center;
  margin-top: 0;
  margin-bottom: 20px;
}

/* Base Card Styling */
.card {
  background: #181818;
  border: 1px solid #2a2a2a;
  padding: 25px;
  border-radius: 12px;
  margin-bottom: 30px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.5);
}

.form-group {
  margin-bottom: 15px;
}

input[type="email"],
input[type="password"],
input[type="text"],
textarea {
  width: 100%;
  padding: 12px;
  background: #222;
  border: 1px solid #333;
  color: #fff;
  border-radius: 6px;
  box-sizing: border-box;
  font-size: 14px;
}

textarea {
  resize: vertical;
}

/* Action Buttons */
.btn-primary, .btn-success, .btn-logout, .btn-delete {
  font-weight: bold;
  cursor: pointer;
  border: none;
  transition: background 0.2s ease;
}

.btn-primary {
  width: 100%;
  background-color: #2563eb;
  color: white;
  padding: 12px;
  border-radius: 6px;
}
.btn-primary:hover { background-color: #1d4ed8; }

.btn-success {
  width: 100%;
  background-color: #10b981;
  color: white;
  padding: 12px;
  border-radius: 6px;
}
.btn-success:hover { background-color: #059669; }

.header-actions {
  display: flex;
  justify-content: flex-end;
  margin-bottom: 15px;
}

.btn-logout {
  background-color: #ef4444;
  color: white;
  padding: 8px 16px;
  border-radius: 6px;
}
.btn-logout:hover { background-color: #dc2626; }

/* Tasks Container Display Styles */
.tasks-section {
  margin-top: 20px;
}

.no-tasks {
  text-align: center;
  color: #888;
  font-style: italic;
  margin-top: 20px;
}

.task-card {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: #181818;
  border: 1px solid #2a2a2a;
  padding: 20px;
  margin-bottom: 15px;
  border-radius: 8px;
  border-left: 4px solid #10b981;
}

.task-info {
  flex-grow: 1;
}

.task-info h3 {
  margin: 0 0 5px 0;
  font-size: 1.2rem;
}

.task-info p {
  margin: 0;
  color: #b3b3b3;
  font-size: 0.95rem;
}

.btn-delete {
  background-color: #ef4444;
  color: white;
  padding: 8px 14px;
  border-radius: 6px;
  margin-left: 20px;
  font-size: 14px;
}
.btn-delete:hover { background-color: #dc2626; }

.error-text {
  color: #ef4444;
  text-align: center;
  margin-top: 15px;
  font-size: 14px;
}
</style>