module.exports = {
  mode: 'jit',
  important: ':is(.wp-editor-content, .editor-styles-wrapper)',     // Classic + block editor iframe
  corePlugins: { preflight: false },   // <— keep off to avoid nuking TinyMCE defaults
  content: ['assets/css/editor.css'],
  theme: { extend: {} },
  plugins: [require('@tailwindcss/typography')]
};