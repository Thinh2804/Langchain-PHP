import os
from langchain_community.document_loaders import PyPDFLoader
from langchain_community.vectorstores import FAISS
from langchain_openai import OpenAIEmbeddings
from langchain_openai import ChatOpenAI
from langchain.text_splitter import RecursiveCharacterTextSplitter
from langchain.chains import RetrievalQA
from langchain.prompts import PromptTemplate
from langchain.memory import ConversationBufferMemory
import json
from datetime import datetime
from collections import defaultdict


# Khởi tạo mô hình ngôn ngữ
llm = ChatOpenAI(
    model="gpt-3.5-turbo-16k",
    temperature=0.7,
    max_tokens=500,
    timeout=None,
    max_retries=2,
)

# Hàm để tải và xử lý tất cả các tệp PDF trong một thư mục
def load_documents_from_directory(directory):
    documents = []
    for filename in os.listdir(directory):
        if filename.lower().endswith('.pdf'):
            file_path = os.path.join(directory, filename)
            try:
                loader = PyPDFLoader(file_path)
                docs = loader.load_and_split()
                for doc in docs:
                    doc.metadata['source'] = filename
                documents.extend(docs)
                print(f"Đã tải thành công: {filename}")
            except Exception as e:
                print(f"Lỗi khi tải {filename}: {str(e)}")
    
    if not documents:
        print("Không tìm thấy tệp PDF nào trong thư mục.")
    else:
        print(f"Đã tải tổng cộng {len(documents)} phần từ các tệp PDF.")
    
    return documents

# Hàm để lưu lịch sử trò chuyện
def save_chat_history(history):
    timestamp = datetime.now().strftime("%Y%m%d_%H%M%S")
    filename = f"chat_history_{timestamp}.json"
    with open(filename, 'w', encoding='utf-8') as f:
        json.dump(history, f, indent=2, ensure_ascii=False)
    print(f"Lịch sử trò chuyện đã được lưu vào {filename}")

# Hàm để tải lịch sử trò chuyện
def load_chat_history(filename):
    try:
        with open(filename, 'r', encoding='utf-8') as f:
            return json.load(f)
    except FileNotFoundError:
        return []

# Thư mục chứa các tệp PDF
data_directory = "data"

# Tải tất cả tài liệu từ thư mục "data"
all_documents = load_documents_from_directory(data_directory)

# Tạo text splitter để chia nhỏ tài liệu
text_splitter = RecursiveCharacterTextSplitter(
    chunk_size=1000,
    chunk_overlap=200,
    length_function=len,
)

# Chia nhỏ tài liệu thành các đoạn nhỏ
split_docs = text_splitter.split_documents(all_documents)

# Tạo embeddings và FAISS từ tất cả các tài liệu
embeddings = OpenAIEmbeddings()
vectorstore = FAISS.from_documents(split_docs, embeddings)

# Tạo một prompt template chi tiết hơn
prompt_template = """Sử dụng thông tin sau đây để trả lời câu hỏi. Nếu bạn không thể trả lời câu hỏi bằng thông tin được cung cấp, hãy nói "Tôi không có đủ thông tin để trả lời câu hỏi này." Trả lời bằng tiếng Việt và đảm bảo câu trả lời ngắn gọn, súc tích.

Thông tin: {context}

Câu hỏi: {question}

Trả lời tiếng Việt:"""

PROMPT = PromptTemplate(
    template=prompt_template, input_variables=["context", "question"]
)

# Cập nhật cấu hình của vectorstore retriever
retriever = vectorstore.as_retriever(search_type="mmr", search_kwargs={"k": 5, "fetch_k": 10})

# Tạo RetrievalQA chain
qa_chain = RetrievalQA.from_chain_type(
    llm=llm,
    chain_type="stuff",
    retriever=retriever,
    return_source_documents=True,
    chain_type_kwargs={"prompt": PROMPT}
)

# Hàm để trả lời câu hỏi bằng tiếng Việt và trả về nguồn tham khảo
def answer_question_in_vietnamese(question):
    result = qa_chain({"query": question})
    answer = result["result"]
    source_documents = result["source_documents"]
    
    sources = defaultdict(set)
    for doc in source_documents:
        source = doc.metadata.get('source', 'Không xác định')
        page = doc.metadata.get('page', 'N/A')
        if page != 'N/A':
            sources[source].add(page)
    
    return answer, sources

# Tải lịch sử trò chuyện trước đó nếu có
history_file = "chat_history.json"
chat_history = load_chat_history(history_file)

# Vòng lặp chatbot
while True:
    query = input("Hỏi một câu hỏi (hoặc gõ 'quit' để thoát): ")
    if query.lower() == 'quit':
        break
    
    # Thêm câu hỏi của người dùng vào lịch sử trò chuyện
    chat_history.append({"role": "user", "content": query})
    
    # Tạo câu trả lời bằng tiếng Việt và lấy nguồn tham khảo
    answer, sources = answer_question_in_vietnamese(query)
    
    # Thêm câu trả lời của bot vào lịch sử trò chuyện
    chat_history.append({"role": "assistant", "content": answer})
    
    print(f"Câu trả lời: {answer}")
    
    # Hiển thị nguồn tham khảo
    if sources:
        print("\nNguồn tham khảo:")
        for source, pages in sources.items():
            pages_str = ', '.join(map(str, sorted(pages)))
            print(f"{source}: Trang {pages_str}")
    else:
        print("\nKhông tìm thấy nguồn tham khảo phù hợp.")

# Lưu lịch sử trò chuyện
save_chat_history(chat_history)