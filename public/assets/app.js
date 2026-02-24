(function () {
  const cs = document.getElementById('card-sorting');
  if (cs) {
    const categories = document.getElementById('categories');
    const cards = document.getElementById('cards');
    let dragged = null;

    function bindCard(card) {
      card.addEventListener('dragstart', () => (dragged = card));
    }

    function bindZone(zone) {
      zone.addEventListener('dragover', (e) => e.preventDefault());
      zone.addEventListener('drop', () => {
        if (dragged) zone.appendChild(dragged);
      });
    }

    document.querySelectorAll('.card').forEach(bindCard);
    document.querySelectorAll('.dropzone').forEach(bindZone);

    const addBtn = document.getElementById('add-category');
    if (addBtn) {
      addBtn.addEventListener('click', () => {
        const name = prompt('Nombre de categoría');
        if (!name) return;
        const div = document.createElement('div');
        div.className = 'border rounded p-2 min-h-32 dropzone';
        div.dataset.category = name;
        div.innerHTML = `<h3 class="font-semibold"></h3>`;
        div.querySelector('h3').textContent = name;
        categories.appendChild(div);
        bindZone(div);
      });
    }

    cs.closest('div').querySelector('form').addEventListener('submit', (e) => {
      const payload = {};
      document.querySelectorAll('.dropzone').forEach((zone) => {
        const cat = zone.dataset.category;
        zone.querySelectorAll('.card').forEach((card) => {
          payload[card.dataset.cardId] = cat;
        });
      });
      const allCards = document.querySelectorAll('.card').length;
      if (Object.keys(payload).length !== allCards) {
        e.preventDefault();
        alert('Debes categorizar todas las tarjetas.');
        return;
      }
      document.getElementById('payload').value = JSON.stringify(payload);
    });
  }

  const tt = document.getElementById('tree-testing');
  if (tt) {
    const tasks = JSON.parse(tt.dataset.tasks || '[]');
    const box = document.getElementById('task-box');
    const nodes = document.querySelectorAll('#tree .node');
    const next = document.getElementById('next-task');
    const answersInput = document.getElementById('answers');
    let current = 0;
    let selected = null;
    let startedAt = performance.now();
    const answers = [];

    function renderTask() {
      if (current >= tasks.length) {
        answersInput.value = JSON.stringify(answers);
        document.getElementById('tree-form').submit();
        return;
      }
      box.textContent = `Tarea ${current + 1}: ${tasks[current].prompt}`;
      selected = null;
      nodes.forEach((n) => n.classList.remove('bg-green-100'));
      startedAt = performance.now();
    }

    nodes.forEach((node) => {
      node.addEventListener('click', () => {
        nodes.forEach((n) => n.classList.remove('bg-green-100'));
        node.classList.add('bg-green-100');
        selected = node;
      });
    });

    next.addEventListener('click', () => {
      if (!selected) return alert('Selecciona un nodo');
      const path = selected.textContent.replace('•', '').trim();
      answers.push({
        task_id: tasks[current].id,
        selected_node_id: selected.dataset.id,
        path_text: path,
        time_ms: Math.round(performance.now() - startedAt),
      });
      current += 1;
      renderTask();
    });

    renderTask();
  }
})();
