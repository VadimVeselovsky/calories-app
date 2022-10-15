import Vue from 'vue'
import VueRouter from 'vue-router'
import TrackerIndex from '../views/tracker/Index.vue'
import AdminIndex from "../views/admin/Index"
import Meal from '../views/Meal'

Vue.use(VueRouter)

const routes = [
  {
    path: '/tracker',
    name: 'Tracker',
    component: TrackerIndex
  },
  {
    path: '/tracker/meals/create',
    name: 'TrackerCreateMeal',
    component: Meal
  },
  {
    path: '/tracker/meals/:id',
    name: 'TrackerUpdateMeal',
    component: Meal
  },
  {
    path: '/admin',
    name: 'Admin',
    component: AdminIndex
  },
  {
    path: '/admin/meals/create',
    name: 'AdminCreateMeal',
    component: Meal
  },
  {
    path: '/admin/meals/:id',
    name: 'AdminUpdateMeal',
    component: Meal
  }
]

const redirects = [
  {
    path: '/',
    redirect: '/tracker'
  },
]


const router = new VueRouter({
  mode: 'history',
  base: process.env.BASE_URL,
  routes: routes.concat(redirects)
})

export default router
