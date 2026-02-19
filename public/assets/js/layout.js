// Simple Layout System
let widgets = document.querySelectorAll('.widget');
widgets.forEach(w => {
    w.draggable = true;
    w.addEventListener('dragend', function(e) {
        this.style.position = 'absolute';
        this.style.left = e.clientX + 'px';
        this.style.top = e.clientY + 'px';
    });
});