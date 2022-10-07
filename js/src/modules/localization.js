export default function getLocalization(stringKey) {
  if (typeof window.matrix_starter_screenReaderText === 'undefined' || typeof window.matrix_starter_screenReaderText[stringKey] === 'undefined') {
    // eslint-disable-next-line no-console
    console.error(`Missing translation for ${stringKey}`);
    return '';
  }
  return window.matrix_starter_screenReaderText[stringKey];
}
