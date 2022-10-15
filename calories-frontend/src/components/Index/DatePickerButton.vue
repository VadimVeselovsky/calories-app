<template>
  <v-menu
    left
    ref="menu"
    v-model="isOpen"
    :close-on-content-click="false"
    transition="scale-transition"
    min-width="auto"
  >
    <template v-slot:activator="{ on }">
      <v-btn v-on="on" fab text small>
        <v-icon>mdi-calendar</v-icon>
      </v-btn>
    </template>
    <v-date-picker
      range
      v-model="date"
      :max="
        new Date(Date.now() - new Date().getTimezoneOffset() * 60000)
          .toISOString()
          .substr(0, 10)
      "
      min="1950-01-01"
      @click:date="onChange"
    ></v-date-picker>
  </v-menu>
</template>

<script>
export default {
  props: {
    callback: {
      type: Function,
      required: true,
    },
  },

  data() {
    return {
      date: [],
      isOpen: false,
    };
  },

  methods: {
    onChange() {
      this.callback(this.date);
    },
  },
};
</script>