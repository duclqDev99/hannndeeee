<!-- DropdownOption.vue -->
<template>
    <li @click="selectItem">
        <img :src="`/storage/${item.image}`" alt="" width="50" height="50">
        {{ item.name }}
    </li>
</template>
  
<script>
export default {
    props: {
        item: Object,
        selectItem: Function,
    },
};
</script>

  