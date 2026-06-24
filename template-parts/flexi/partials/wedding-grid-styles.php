<style>
  .grid-container { display: grid; gap: 1.5rem; grid-template-columns: repeat(3, 1fr); max-width: 1364px; margin: auto; }
  @media (max-width: 1124px) { .grid-container { grid-template-columns: repeat(2, 1fr); } }
  @media (max-width: 768px) { .grid-container { grid-template-columns: repeat(1, 1fr); } }
  .grid-item { display: flex; flex-direction: column; overflow: hidden; border: 4px solid black; border-radius: 12px; box-shadow: 2px 4px 10px rgba(0, 0, 0, 0.1); }
  .image-container { position: relative; overflow: hidden; width: 100%; height: 600px; }
  .main-image { width: 100%; height: 100%; object-fit: cover; }
  .hover-image { position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover; opacity: 0; transition: opacity 0.3s ease-in-out; }
  .image-container:hover .hover-image { opacity: 1; }
  .product-details { padding: 1rem; background: white; }
</style>
