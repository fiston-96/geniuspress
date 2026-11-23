import { createRoot } from 'react-dom/client';
import App from './App';
import { setState } from './store';

const boot = JSON.parse(document.getElementById('gp-boot').textContent);

setState({
    content: boot.page.content?.sections ? boot.page.content : { version: 1, sections: [] },
    status: boot.page.status,
    hasDraft: boot.page.has_draft,
    revisions: boot.revisions,
});

createRoot(document.getElementById('builder')).render(<App boot={boot} />);
