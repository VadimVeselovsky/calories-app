import Vue from 'vue'
import Vuex from 'vuex'

Vue.use(Vuex)

export default new Vuex.Store({
  state: {
  },
  getters: {
    maximums() {
      return {
        calories: 2100,
        money: 1000
      }
    }
  },
  mutations: {
  },
  actions: {
  },
  modules: {
  }
})
