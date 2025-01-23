document.addEventListener("DOMContentLoaded", function() {
    // Select the blog title element
    const blogTitle = document.querySelector('.bottom-closer .inner .blog-title');

    if (blogTitle) {
      // Get the text content of the blog title
      const text = blogTitle.textContent;

      // Split the text into words
      const words = text.split(' ');

      // Wrap the first letter of each word in a span with the class 'accent'
      const newText = words.map(word => {
        return `<span class="accent">${word[0]}</span>${word.slice(1)}`;
      }).join(' ');

      // Update the blog title HTML
      blogTitle.innerHTML = newText;
    }
  });
