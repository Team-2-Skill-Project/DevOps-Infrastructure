# AI Service CI Failure & Dependency Report
**Target System:** Python 3.11 / FastAPI / Redis / Uvicorn  
**CI Workflow:** `CI — Test All Services` (`ai-tests` job)  
**Status:** Fails on clean checkout due to missing packages in `requirements.txt`

---

## Executive Summary for the AI Team
The DevOps automated CI pipeline executes:
```bash
pip install -r requirements.txt
python -m pytest tests/ -v --tb=short
```
When running against a fresh virtual environment from a clean checkout of `origin/main` ([`a64767c`](file:///home/omar/Projects/SkillMatch/AI)), the service **cannot start or run its tests** because **7 critical runtime dependencies** are imported in application code and tests but are missing from [`requirements.txt`](file:///home/omar/Projects/SkillMatch/AI/requirements.txt).

Furthermore, in [`src/core/llm.py`](file:///home/omar/Projects/SkillMatch/AI/src/core/llm.py), the provider fallback resolution logic requires attention to avoid model prefix resolution errors during testing.

---

## 1. Missing Dependencies in `requirements.txt`

The current [`requirements.txt`](file:///home/omar/Projects/SkillMatch/AI/requirements.txt) contains 17 packages:
```text
pydantic>=2.5.0
pypdf>=4.0.0
python-docx>=1.1.0
google-generativeai>=0.4.0
python-dotenv>=1.0.0
pytest>=8.0.0
fastapi>=0.110.0
uvicorn>=0.28.0
python-multipart>=0.0.9
qdrant-client>=1.19.0
langgraph>=1.2.0
langchain-core>=0.3.0
langchain-community>=0.3.0
sqlalchemy>=2.0.0
celery>=5.3.0
httpx>=0.27.0
pytest-asyncio>=0.23.0
```

However, the application codebase imports the following unlisted packages:

| Missing Dependency | Where It Is Imported | Failure Symptom |
| :--- | :--- | :--- |
| **`pydantic-settings>=2.0.0`** | [`src/core/config.py:18`](file:///home/omar/Projects/SkillMatch/AI/src/core/config.py#L18): `from pydantic_settings import BaseSettings, SettingsConfigDict` | `ModuleNotFoundError: No module named 'pydantic_settings'` on application boot. |
| **`redis>=5.0.0`** | [`src/core/redis.py:5`](file:///home/omar/Projects/SkillMatch/AI/src/core/redis.py#L5): `import redis`<br>[`src/api/main.py:29`](file:///home/omar/Projects/SkillMatch/AI/src/api/main.py#L29)<br>[`tests/test_redis.py:7`](file:///home/omar/Projects/SkillMatch/AI/tests/test_redis.py#L7) | `ModuleNotFoundError: No module named 'redis'` on boot and during `test_redis.py`. |
| **`groq>=0.9.0`** | [`src/api/main.py:7`](file:///home/omar/Projects/SkillMatch/AI/src/api/main.py#L7): `import groq`<br>[`tests/test_logging_and_error_handling.py:4`](file:///home/omar/Projects/SkillMatch/AI/tests/test_logging_and_error_handling.py#L4) | `ModuleNotFoundError: No module named 'groq'`. |
| **`langchain-groq>=0.2.0`** | [`src/core/llm.py:12`](file:///home/omar/Projects/SkillMatch/AI/src/core/llm.py#L12): `from langchain_groq import ChatGroq` | `ModuleNotFoundError: No module named 'langchain_groq'` when instantiating Groq LLM. |
| **`langchain-google-genai>=2.0.0`**| [`src/core/llm.py:14`](file:///home/omar/Projects/SkillMatch/AI/src/core/llm.py#L14): `from langchain_google_genai import ChatGoogleGenerativeAI` | `ModuleNotFoundError: No module named 'langchain_google_genai'` when using Gemini LangChain adapter. |
| **`langchain>=0.3.0`** | LangGraph orchestration and chaining pipelines | Missing top-level LangChain package. |
| **`duckduckgo_search>=6.0.0`** | [`src/workers/youtube_fetcher.py:84`](file:///home/omar/Projects/SkillMatch/AI/src/workers/youtube_fetcher.py#L84): `from duckduckgo_search import DDGS`<br>[`tests/test_duckduckgo_fetcher.py`](file:///home/omar/Projects/SkillMatch/AI/tests/test_duckduckgo_fetcher.py) | `ModuleNotFoundError: No module named 'duckduckgo_search'`. |
| **`google-api-python-client>=2.100.0`** | [`src/workers/youtube_fetcher.py:20`](file:///home/omar/Projects/SkillMatch/AI/src/workers/youtube_fetcher.py#L20): `from googleapiclient.discovery import build` | `ModuleNotFoundError: No module named 'googleapiclient'`. |

---

## 2. LLM Provider Resolution in `src/core/llm.py`

### Location:
[`src/core/llm.py:85`](file:///home/omar/Projects/SkillMatch/AI/src/core/llm.py#L85)

### Issue:
```python
def get_llm(model: Optional[str] = None, provider: Optional[str] = None, ctx: Optional[Any] = None, ...):
    raw_model = model if model is not None else (ctx.model_name if ctx else None)
    raw_model = raw_model or llm_settings.model_name
    effective_provider = provider or (ctx.provider if ctx else None) or llm_settings.provider
    parsed_provider, model_name = settings.parse_provider_and_model(raw_model, effective_provider)
```

When a raw model string such as `"llama-3.3-70b-versatile"` or a provider-agnostic call is passed in tests, fallback to `llm_settings.provider` can conflict with model name parsing if `settings.parse_provider_and_model` expects `effective_provider` to be unset when not explicitly requested.

### Recommended Fix:
Allow `parse_provider_and_model` to infer the provider from the model string before falling back to default settings:
```python
# src/core/llm.py
effective_provider = provider or (ctx.provider if ctx else None)
if not effective_provider:
    # Let parse_provider_and_model attempt inference from the model name first
    parsed_provider, model_name = settings.parse_provider_and_model(raw_model, None)
    if not parsed_provider:
        parsed_provider = llm_settings.provider
else:
    parsed_provider, model_name = settings.parse_provider_and_model(raw_model, effective_provider)
```

---

## Action Checklist for the AI Team
1. [ ] Update [`requirements.txt`](file:///home/omar/Projects/SkillMatch/AI/requirements.txt) to include all missing packages:
   ```text
   pydantic>=2.5.0
   pydantic-settings>=2.0.0
   pypdf>=4.0.0
   python-docx>=1.1.0
   google-generativeai>=0.4.0
   python-dotenv>=1.0.0
   pytest>=8.0.0
   fastapi>=0.110.0
   uvicorn>=0.28.0
   python-multipart>=0.0.9
   qdrant-client>=1.19.0
   langgraph>=1.2.0
   langchain>=0.3.0
   langchain-core>=0.3.0
   langchain-community>=0.3.0
   langchain-google-genai>=2.0.0
   langchain-groq>=0.2.0
   groq>=0.9.0
   redis>=5.0.0
   duckduckgo_search>=6.0.0
   google-api-python-client>=2.100.0
   sqlalchemy>=2.0.0
   celery>=5.3.0
   httpx>=0.27.0
   pytest-asyncio>=0.23.0
   ```
2. [ ] Test fresh installation in an isolated virtual environment:
   ```bash
   python -m venv test_env && source test_env/bin/activate
   pip install -r requirements.txt
   pytest tests/
   ```
3. [ ] Commit the updated `requirements.txt` to the `AI` repository on branch `main`.
