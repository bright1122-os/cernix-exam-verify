let display = document.getElementById('display');

function appendValue(value) {
  if (display.value === '0' || display.value === 'Error') {
    display.value = value;
  } else {
    display.value += value;
  }
}

function clearDisplay() {
  display.value = '0';
}

function deleteLast() {
  if (display.value.length > 1) {
    display.value = display.value.slice(0, -1);
  } else {
    display.value = '0';
  }
}

function calculateResult() {
  try {
    let expression = display.value;

    // Convert power symbol to JavaScript exponent operator
    expression = expression.replace(/\^/g, '**');

    // Convert percentage to division by 100
    expression = expression.replace(/%/g, '/100');

    display.value = eval(expression);
  } catch (error) {
    display.value = 'Error';
  }
}
