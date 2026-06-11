const loginForm = document.getElementById('loginForm');
const message = document.getElementById('message');

loginForm.addEventListener('submit', function(event) {
  event.preventDefault();

  const username = document.getElementById('username').value.trim();
  const password = document.getElementById('password').value.trim();

  message.className = 'message';

  if (username === '' || password === '') {
    message.textContent = 'Please enter both username and password.';
    message.classList.add('error');
    return;
  }

  if (username === 'student' && password === '12345') {
    message.textContent = 'Login successful. Welcome, student!';
    message.classList.add('success');
  } else {
    message.textContent = 'Invalid username or password.';
    message.classList.add('error');
  }
});

loginForm.addEventListener('reset', function() {
  message.textContent = '';
  message.className = 'message';
});
