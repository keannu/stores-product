import { createRouter, createWebHistory } from 'vue-router';
import Home from '../views/Home.vue';
import Login from '../views/Login/Login.vue';
import Dashboard from '../views/Dashboard/Dashboard.vue';
import Stores from '../views/Stores/Stores.vue';
import Users from '../views/Users/Users.vue';
import NotFound from '../views/NotFound/NotFound.vue';

const routes = [
    { path: '/', component: Home },
    { path: '/login', component: Login },
    { path: '/dashboard', component: Dashboard },
    { path: '/dashboard/stores', component: Stores },
    { path: '/dashboard/users', component: Users },
    { path: '/:pathMatch(.*)*', component: NotFound },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

export default router;
