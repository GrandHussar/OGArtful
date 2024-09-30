<template>
  <div></div>
</template>

<script>
export default {
  data() {
    return {
      history: [],
      historyIndex: -1,
    };
  },
  methods: {
    addToHistory(action) {
      this.history.push(action);
      this.historyIndex = this.history.length - 1;
    },
    undo() {
      if (this.historyIndex >= 0) {
        const lastAction = this.history[this.historyIndex];
        if (lastAction.action === 'add') {
          lastAction.object.remove();
          this.historyIndex--;
        }
      }
    },
    redo() {
      if (this.historyIndex < this.history.length - 1) {
        this.historyIndex++;
        const nextAction = this.history[this.historyIndex];
        if (nextAction.action === 'remove') {
          this.layer.add(nextAction.object);
          this.addToHistory({ action: 'add', object: nextAction.object });
          this.historyIndex++;
        }
      }
    },
  },
};
</script>