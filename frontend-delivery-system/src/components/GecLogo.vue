<template>
  <svg viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-full">
    <!-- Outer circular arrows -->
    <path d="M50 10 A40 40 0 0 1 90 50" stroke="#5cb85c" stroke-width="8" stroke-linecap="round" fill="none"/>
    <path d="M90 50 A40 40 0 0 1 50 90" stroke="#5cb85c" stroke-width="8" stroke-linecap="round" fill="none"/>
    <path d="M50 90 A40 40 0 0 1 10 50" stroke="#5cb85c" stroke-width="8" stroke-linecap="round" fill="none"/>
    <path d="M10 50 A40 40 0 0 1 50 10" stroke="#5cb85c" stroke-width="8" stroke-linecap="round" fill="none"/>
    <!-- Arrow heads -->
    <polygon points="50,4 44,14 56,14" fill="#5cb85c"/>
    <polygon points="96,50 86,44 86,56" fill="#5cb85c"/>
    <!-- Leaf in center -->
    <ellipse cx="50" cy="50" rx="14" ry="20" fill="#3d9a3d" transform="rotate(-15 50 50)"/>
    <line x1="50" y1="35" x2="50" y2="68" stroke="#0d3320" stroke-width="2"/>
    <line x1="50" y1="42" x2="43" y2="50" stroke="#0d3320" stroke-width="1.5"/>
    <line x1="50" y1="50" x2="57" y2="58" stroke="#0d3320" stroke-width="1.5"/>
    <line x1="50" y1="48" x2="43" y2="56" stroke="#0d3320" stroke-width="1.5"/>
  </svg>
</template>
