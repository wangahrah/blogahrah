/**
 * Terminal emulator for wangahrah.com
 * Vanilla JS - no dependencies
 */

class Terminal {
  constructor(elementId) {
    this.terminal = document.getElementById(elementId);
    this.userInput = '';
    this.prompt = 'noob@wangahrah.com~$ ';

    this.init();
  }

  init() {
    document.addEventListener('keydown', (e) => this.handleKeydown(e));
    document.addEventListener('keypress', (e) => this.handleKeypress(e));

    // Setup email obfuscation
    this.setupMailObfuscation();
  }

  handleKeydown(e) {
    // Handle backspace
    if (e.key === 'Backspace') {
      e.preventDefault();
      if (this.userInput.length > 0) {
        this.userInput = this.userInput.slice(0, -1);
        const content = this.terminal.innerHTML;
        this.terminal.innerHTML = content.slice(0, -1);
      }
      return;
    }

    // Handle enter
    if (e.key === 'Enter') {
      this.parseCommand(this.userInput.toUpperCase().trim());
      this.userInput = '';
      return;
    }
  }

  handleKeypress(e) {
    // Ignore enter (handled in keydown)
    if (e.key === 'Enter') return;

    // Append the character
    this.terminal.insertAdjacentHTML('beforeend', e.key);
    this.userInput += e.key;
  }

  print(text) {
    this.terminal.innerHTML = text;
    this.terminal.insertAdjacentHTML('beforeend', `<br>${this.prompt}`);
  }

  parseCommand(command) {
    this.terminal.insertAdjacentHTML('beforeend', '<br>');

    const commands = {
      '': () => this.print("If you don't enter anything, I can't do anything"),

      'LOOK': () => this.print('You can go north, south, or east.'),

      'HELP': () => this.showHelp(),
      'MAN': () => this.showHelp(),
      'MENU': () => this.showHelp(),

      'N': () => this.print('You enter a large room. Oops, the floor gives in. You die. Restarting...'),
      'NORTH': () => this.print('You enter a large room. Oops, the floor gives in. You die. Restarting...'),

      'S': () => this.print('You jump on a waterslide heading south. The waterslide ends with a drop into a pool of spikes. You are dead. Restarting...'),
      'SOUTH': () => this.print('You jump on a waterslide heading south. The waterslide ends with a drop into a pool of spikes. You are dead. Restarting...'),

      'E': () => this.print('The sun beams strongly into this area. So strongly that the radiation melts your flesh. Restarting...'),
      'EAST': () => this.print('The sun beams strongly into this area. So strongly that the radiation melts your flesh. Restarting...'),

      'W': () => this.print('You head west into the darkness. A Grue devours you instantly. Restarting...'),
      'WEST': () => this.print('You head west into the darkness. A Grue devours you instantly. Restarting...'),

      'LS': () => this.print("This ain't no filesystem, bro."),
      'PWD': () => this.print("This is just some hackish JavaScript, there's no directory."),
      'CD': () => this.print("Nice try. There's nowhere to go."),
      'SUDO': () => this.print("You're not in the sudoers file. This incident will be reported."),
      'EXIT': () => this.print("There is no escape from the Grue."),
      'CLEAR': () => { this.terminal.innerHTML = this.prompt; },

      'RESUME': () => this.navigate('resume.php', 'SEARCHING FOR RESUME'),
      'BLOG': () => this.navigate('blogahrah/', 'SEARCHING FOR BLOG'),
      'WIKI': () => this.navigate('lifewiki/', 'SEARCHING FOR WIKI'),
    };

    const action = commands[command];
    if (action) {
      action();
    } else {
      this.print('A Grue eats your command before it can be executed.');
    }
  }

  showHelp() {
    this.print(
      'Type a direction to go in that direction, or look to see where you can go.<br>' +
      'You can also navigate to my resume, blog, or wiki by typing the appropriate destination.'
    );
  }

  navigate(url, message) {
    this.print(message);
    window.location = url;
  }

  setupMailObfuscation() {
    const mailSpans = document.querySelectorAll('span.mailme');

    mailSpans.forEach(span => {
      const text = span.textContent;
      const email = text.replace(/ at /g, '@').replace(/ dot /g, '.');

      const link = document.createElement('a');
      link.href = `mailto:${email}`;
      link.title = 'Send an email';
      link.textContent = email;

      span.after(link);
      span.remove();
    });
  }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
  new Terminal('terminal');
});
