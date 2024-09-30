<template>
    <div class="flex items-center justify-center h-screen bg-gray-100">
      <div class="bg-white p-8 rounded-lg shadow-lg text-center">
        <h1 class="text-2xl font-bold mb-4">Account Restricted</h1>
        <p class="mb-4">Your account is restricted until:</p>
        <p class="text-xl font-semibold text-red-600">{{ formattedRestrictionEndAt }}</p>
      </div>
    </div>
  </template>
  
  <script setup>
  import { usePage } from '@inertiajs/vue3';
  import moment from 'moment';
  
  const { props } = usePage();
  const restrictionEndAt = props.restriction_end_at || 'Unknown';
  
  const formattedRestrictionEndAt = restrictionEndAt !== 'Unknown'
    ? moment(restrictionEndAt).format('MMMM Do YYYY')
    : 'Unknown';
  </script>
  
  <style scoped>
  /* Add any additional styling here */
  </style>
  