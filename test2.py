import os
from langchain_community.document_loaders import PyPDFLoader, UnstructuredImageLoader 
from langchain_community.vectorstores import FAISS
from langchain_openai import OpenAIEmbeddings
from langchain_openai import ChatOpenAI
from langchain.text_splitter import RecursiveCharacterTextSplitter
from langchain.chains import ConversationalRetrievalChain
from langchain.memory import ConversationBufferMemory
import json
from datetime import datetime


from dotenv import load_dotenv  
import os

load_dotenv()  # Đọc file .env và thiết lập biến môi trường

# Khởi tạo mô hình ngôn ngữ
llm = ChatOpenAI(
    model="gpt-3.5-turbo-16k",
    temperature=0.7,
    max_tokens=500,
    timeout=None,
    max_retries=2,
    
)

# Hàm để tải và xử lý tất cả các tệp PDF và hình ảnh trong một thư mục
def load_documents_from_directory(directory):
    documents = []
    for filename in os.listdir(directory):
        file_path = os.path.join(directory, filename)
        if filename.endswith('.pdf'):
            loader = PyPDFLoader(file_path)
            docs = loader.load_and_split()
            for doc in docs:
                doc.metadata['source'] = filename
            documents.extend(docs)
        elif filename.lower().endswith(('.png', '.jpg', '.jpeg', '.gif', '.bmp')):
            loader = UnstructuredImageLoader(file_path)
            docs = loader.load()
            for doc in docs:
                doc.metadata['source'] = filename
            documents.extend(docs)
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

# Thư mục chứa các tệp PDF và hình ảnh
data_directory = "data"

# Tải tất cả tài liệu
all_documents = load_documents_from_directory(data_directory)

# Tạo text splitter
text_splitter = RecursiveCharacterTextSplitter(
    chunk_size=1000,
    chunk_overlap=200,
    length_function=len,
)

# Chia nhỏ tài liệu
split_docs = text_splitter.split_documents(all_documents)

# Tạo chỉ mục FAISS
embeddings = OpenAIEmbeddings()
vectorstore = FAISS.from_documents(split_docs, embeddings)

# Tạo memory để lưu trữ lịch sử hội thoại
memory = ConversationBufferMemory(
    memory_key="chat_history",
    return_messages=True,
    output_key="answer"  # Chỉ định khóa đầu ra cụ thể
)

# Tạo chuỗi QA với ConversationalRetrievalChain
qa_chain = ConversationalRetrievalChain.from_llm(
    llm=llm,
    retriever=vectorstore.as_retriever(search_kwargs={"k": 3}),
    memory=memory,
    get_chat_history=lambda h: h,
    return_source_documents=True,
    verbose=True,
)

# Hàm để trả lời câu hỏi bằng tiếng Việt và trả về nguồn tham khảo
def answer_question_in_vietnamese(question):
    prompt = f"""Dựa trên thông tin trong các tài liệu PDF và hình ảnh, hãy trả lời câu hỏi sau một cách chính xác và linh hoạt bằng tiếng Việt. Nếu câu trả lời không có trong tài liệu, hãy nói rằng bạn không có thông tin về điều đó. Câu hỏi: {question}

Hãy đảm bảo:
1. Trả lời chính xác dựa trên nội dung trong tài liệu.
2. Nếu câu hỏi không liên quan đến nội dung trong tài liệu, hãy nói rằng bạn không có thông tin về điều đó.
3. Trả lời một cách tự nhiên và linh hoạt, không chỉ lặp lại nguyên văn từ tài liệu.
4. Nếu cần thiết, hãy kết hợp thông tin từ nhiều phần của tài liệu để đưa ra câu trả lời đầy đủ.
5. Sử dụng ngôn ngữ phù hợp và dễ hiểu với người đọc.
6. Giới hạn câu trả lời trong khoảng 2-3 câu.

Câu trả lời:"""

    result = qa_chain({"question": prompt})
    answer = result["answer"]
    source_documents = result["source_documents"]
    
    return answer, source_documents

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
    answer, source_documents = answer_question_in_vietnamese(query)
    
    # Thêm câu trả lời của bot vào lịch sử trò chuyện
    chat_history.append({"role": "assistant", "content": answer})
    
    print(f"Câu trả lời: {answer}")
    
    # Hiển thị nguồn tham khảo
    print("\nNguồn tham khảo:")
    for i, doc in enumerate(source_documents):
        print(f"Nguồn {i+1}: {doc.metadata.get('source', 'Không có thông tin')}, Trang {doc.metadata.get('page', 'N/A')}")

# Lưu lịch sử trò chuyện
save_chat_history(chat_history)